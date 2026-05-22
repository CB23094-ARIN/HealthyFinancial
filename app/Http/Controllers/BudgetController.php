<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AICategorizer;
use App\Services\ReceiptScanner;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BudgetController extends Controller
{
    protected array $transactionCategories = [
        'Food',
        'Beverages',
        'Living expenses',
        'Transportation expenses',
        'Family care',
        'Personal care',
        'Health care',
        'Technology',
        'Debt payments',
        'Savings and investments',
        'Others',
    ];

    public function dashboard(): View
    {
        $user = Auth::user();
        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get();

        $totalSpent = Transaction::where('user_id', $user->id)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $remaining = $user->monthly_allowance - $totalSpent;

        $weekly = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [now()->subDays(7), now()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $ai = new AICategorizer();
        $insight = $ai->getSpendingInsight($weekly->toArray(), $user->monthly_allowance);
        $healthScore = $this->calculateHealthScore($user);
        $transactionCategories = $this->transactionCategories;

        return view('dashboard', compact('transactions', 'totalSpent', 'remaining', 'insight', 'healthScore', 'transactionCategories'));
    }

    public function showProfile(): View
    {
        return view('profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'campus' => ['nullable', 'string', 'max:100'],
            'monthly_budget' => ['required', 'numeric', 'min:0'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'campus' => $validated['campus'] ?? null,
            'monthly_allowance' => $validated['monthly_budget'],
        ]);

        $this->updateUserProgress($user);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Account and budget updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Password updated.');
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'category' => ['required', Rule::in($this->transactionCategories)],
            'transaction_date' => 'required|date|before_or_equal:today',
        ]);

        $ai = new AICategorizer();
        $category = $request->category;

        Transaction::create([
            'user_id' => Auth::id(),
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $category,
            'transaction_date' => $request->transaction_date,
            'is_auto_categorized' => false,
        ]);

        $this->updateUserProgress(Auth::user());

        return redirect()
            ->route('dashboard')
            ->with('success', 'Transaction added! ' . $ai->getTransactionMessage($request->description, $request->amount, $category));
    }

    public function canAfford(Request $request): View
    {
        $request->validate([
            'item_name' => 'required|string',
            'item_price' => 'required|numeric|min:0.01',
        ]);

        $user = Auth::user();
        $totalSpent = Transaction::where('user_id', $user->id)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $remaining = $user->monthly_allowance - $totalSpent;
        $price = (float) $request->item_price;
        $itemName = $request->item_name;

        if ($price <= $remaining) {
            $answer = "Yes, you can afford {$itemName}.";
            $advice = 'You will have RM' . number_format($remaining - $price, 2) . ' left for the rest of the month.';
        } else {
            $shortfall = $price - $remaining;
            $answer = "Be careful. {$itemName} would put you over budget by RM" . number_format($shortfall, 2) . '.';

            if ($remaining < 0) {
                $advice = 'You are already over budget. Review recent large transactions before saving for this.';
            } else {
                $daysNeeded = (int) ceil($shortfall / 5);
                $advice = $daysNeeded > 365
                    ? 'That gap is very large. Check for accidental big transactions or choose a cheaper option first.'
                    : "Try saving RM5 per day for {$daysNeeded} days, then buy it.";
            }
        }

        $ai = new AICategorizer();
        $funMessage = $ai->getFunMessage($itemName, $price, $remaining);

        return view('can-afford', compact('answer', 'advice', 'funMessage'));
    }

    public function showCanAffordForm(): View
    {
        return view('can-afford');
    }

    public function uploadReceipt(Request $request): RedirectResponse
    {
        $request->validate(['receipt' => 'required|image|max:5120']);

        $scanner = new ReceiptScanner();

        try {
            $items = $scanner->scan($request->file('receipt'));
        } catch (Throwable $exception) {
            return back()->withErrors(['receipt' => $exception->getMessage()]);
        }

        if (count($items) === 0) {
            return back()->withErrors(['receipt' => 'No receipt items found. Try a clearer photo.']);
        }

        foreach ($items as $item) {
            Transaction::create([
                'user_id' => Auth::id(),
                'description' => $item['description'] ?? 'Unknown item',
                'amount' => $item['amount'] ?? 0,
                'category' => $item['category'] ?? 'other',
                'transaction_date' => now(),
                'is_auto_categorized' => true,
            ]);
        }

        $this->updateUserProgress(Auth::user());

        return redirect()
            ->route('dashboard')
            ->with('success', 'Receipt scanned! Added ' . count($items) . ' items.');
    }

    public function showScanReceipt(): View
    {
        return view('scan-receipt');
    }

    public function leaderboard(): View
    {
        User::whereHas('transactions')->get()->each(function (User $user): void {
            $this->updateUserProgress($user);
        });

        $leaderboard = Leaderboard::with('user')
            ->orderBy('points', 'desc')
            ->take(20)
            ->get();

        return view('leaderboard', compact('leaderboard'));
    }

    public function transactions(Request $request): View
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->category);
            })
            ->orderBy('transaction_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('transactions', [
            'transactions' => $transactions,
            'transactionCategories' => $this->transactionCategories,
            'selectedCategory' => $request->category,
            'search' => $request->search,
        ]);
    }

    protected function updateUserProgress($user): void
    {
        $user->saving_streak = $this->calculateSavingStreak($user);
        $user->save();

        $this->syncLeaderboard($user);
    }

    protected function calculateSavingStreak($user): int
    {
        $dates = Transaction::where('user_id', $user->id)
            ->whereDate('transaction_date', '<=', now()->toDateString())
            ->selectRaw('DATE(transaction_date) as activity_date')
            ->distinct()
            ->orderByDesc('activity_date')
            ->pluck('activity_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $latestDate = Carbon::parse($dates->first())->startOfDay();

        if ($latestDate->lt(now()->subDay()->startOfDay())) {
            return 0;
        }

        $streak = 0;
        $expectedDate = $latestDate->copy();

        foreach ($dates as $date) {
            $activityDate = Carbon::parse($date)->startOfDay();

            if ($activityDate->equalTo($expectedDate)) {
                $streak++;
                $expectedDate->subDay();
                continue;
            }

            if ($activityDate->lt($expectedDate)) {
                break;
            }
        }

        return $streak;
    }

    protected function syncLeaderboard($user): void
    {
        $transactionCount = Transaction::where('user_id', $user->id)->count();
        $activeDays = Transaction::where('user_id', $user->id)
            ->whereDate('transaction_date', '<=', now()->toDateString())
            ->selectRaw('DATE(transaction_date) as activity_date')
            ->distinct()
            ->get()
            ->count();

        $points = $transactionCount + ($activeDays * 10) + ((int) floor($user->saving_streak / 7) * 25);

        Leaderboard::updateOrCreate(
            ['user_id' => $user->id],
            ['campus' => $user->campus ?? 'Unknown', 'points' => $points]
        );
    }

    protected function calculateHealthScore($user): int
    {
        $totalSpent = Transaction::where('user_id', $user->id)
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        if ($user->monthly_allowance == 0) {
            return 50;
        }

        $spendRatio = $totalSpent / $user->monthly_allowance;
        $score = 100 - ($spendRatio * 100);
        $score += min($user->saving_streak, 20);

        return (int) round(max(0, min(100, $score)));
    }
}
