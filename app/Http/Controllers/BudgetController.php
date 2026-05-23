<?php

namespace App\Http\Controllers;

use App\Models\Leaderboard;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AICategorizer;
use App\Services\PtptnMode;
use App\Services\ReceiptScanner;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
        $this->refreshSavingStreak($user);
        $user->refresh();

        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get();

        $totalSpent = $this->monthlySpentThisMonth($user);
        $ptptnMetrics = (new PtptnMode())->metrics($user, (float) $totalSpent);
        $remaining = $ptptnMetrics['enabled']
            ? $ptptnMetrics['remaining']
            : $user->monthly_allowance - $totalSpent;

        $weekly = Transaction::where('user_id', $user->id)
            ->whereBetween('transaction_date', [now()->subDays(7), now()])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $ai = new AICategorizer();
        $budgetRunway = $this->budgetRunwayMetrics($user, (float) $totalSpent, $ptptnMetrics);
        $budgetRunwayNote = $this->budgetRunwayNote($ai, $budgetRunway, $user->id);
        $dashboardIntro = $ptptnMetrics['enabled']
            ? $this->ptptnDashboardIntro($ai, $ptptnMetrics, $user->id)
            : 'Track spending, update your budget, and keep your profile current.';
        $insight = $ptptnMetrics['enabled']
            ? $ptptnMetrics['message']
            : $ai->getSpendingInsight($weekly->toArray(), $user->monthly_allowance);
        $healthScore = $this->calculateHealthScore($user);
        $transactionCategories = $this->transactionCategories;

        return view('dashboard', compact('transactions', 'totalSpent', 'remaining', 'insight', 'healthScore', 'ptptnMetrics', 'budgetRunway', 'budgetRunwayNote', 'dashboardIntro', 'transactionCategories'));
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
            'university_name' => ['nullable', 'string', 'max:100'],
            'monthly_budget' => ['required', 'numeric', 'min:0'],
            'ptptn_mode' => ['nullable', 'boolean'],
            'ptptn_balance' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'university_name' => $validated['university_name'] ?? null,
            'monthly_allowance' => $validated['monthly_budget'],
            'ptptn_mode' => $request->boolean('ptptn_mode'),
            'ptptn_balance' => $validated['ptptn_balance'] ?? 0,
        ]);

        $this->updateUserProgress($user);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Account, budget, and PTPTN settings updated.');
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
        $totalSpent = $this->monthlySpentThisMonth($user);

        $price = (float) $request->item_price;
        $itemName = $request->item_name;
        $ptptnMode = new PtptnMode();
        $ptptnMetrics = $ptptnMode->metrics($user, (float) $totalSpent);
        $remaining = $ptptnMetrics['enabled']
            ? $ptptnMetrics['remaining']
            : $user->monthly_allowance - $totalSpent;
        $ptptnDecision = $ptptnMode->affordability($user, $itemName, $price, (float) $totalSpent);

        if ($ptptnDecision) {
            $answer = $ptptnDecision['answer'];
            $advice = $ptptnDecision['advice'];
        } elseif ($price <= $remaining) {
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

        return view('can-afford', compact('answer', 'advice', 'funMessage', 'ptptnMetrics'));
    }

    public function showCanAffordForm(): View
    {
        $user = Auth::user();
        $ptptnMetrics = (new PtptnMode())->metrics($user, $this->monthlySpentThisMonth($user));

        return view('can-afford', compact('ptptnMetrics'));
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
        $this->refreshSavingStreak($user);
        $this->syncLeaderboard($user);
    }

    protected function refreshSavingStreak(User $user): void
    {
        $streak = $this->calculateSavingStreak($user);

        if ((int) $user->saving_streak === $streak) {
            return;
        }

        $user->saving_streak = $streak;
        $user->save();
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

        $ptptnBonus = (new PtptnMode())->leaderboardBonus($user, $this->monthlySpentThisMonth($user));
        $points = $transactionCount + ($activeDays * 10) + ((int) floor($user->saving_streak / 7) * 25) + $ptptnBonus;

        Leaderboard::updateOrCreate(
            ['user_id' => $user->id],
            ['university_name' => $user->university_name ?? 'Unknown', 'points' => $points]
        );
    }

    protected function ptptnHourlyNote(AICategorizer $ai, array $ptptnMetrics, int $userId): string
    {
        $cacheKey = 'ptptn-hourly-note:' . $userId . ':' . now()->format('Y-m-d-H');

        try {
            return Cache::remember(
                $cacheKey,
                now()->copy()->endOfHour(),
                fn () => $ai->getPtptnHourlyNote($ptptnMetrics, $userId)
            );
        } catch (Throwable) {
            return $ai->getPtptnHourlyNote($ptptnMetrics, $userId);
        }
    }

    protected function ptptnDashboardIntro(AICategorizer $ai, array $ptptnMetrics, int $userId): string
    {
        $cacheKey = 'ptptn-dashboard-intro:' . $userId . ':' . now()->format('Y-m-d-H');

        try {
            return Cache::remember(
                $cacheKey,
                now()->copy()->endOfHour(),
                fn () => $ai->getPtptnDashboardIntro($ptptnMetrics, $userId)
            );
        } catch (Throwable) {
            return $ai->getPtptnDashboardIntro($ptptnMetrics, $userId);
        }
    }

    protected function budgetRunwayMetrics(User $user, float $totalSpent, array $ptptnMetrics): array
    {
        $monthlyBudget = (float) $user->monthly_allowance;
        $ptptnEnabled = (bool) data_get($ptptnMetrics, 'enabled', false);
        $daysLeft = (int) data_get($ptptnMetrics, 'days_left', max(1, now()->daysInMonth - now()->day + 1));
        $totalAvailable = $ptptnEnabled
            ? (float) data_get($ptptnMetrics, 'total_available', $monthlyBudget)
            : $monthlyBudget;
        $remaining = $ptptnEnabled
            ? (float) data_get($ptptnMetrics, 'remaining', $totalAvailable - $totalSpent)
            : $monthlyBudget - $totalSpent;
        $dailySafeSpend = max(0, $remaining) / max(1, $daysLeft);

        $status = match (true) {
            $totalAvailable <= 0 => 'setup_needed',
            $remaining < 0 => 'over_budget',
            $dailySafeSpend <= 0 => 'no_daily_budget',
            $dailySafeSpend < 10 => 'tight',
            default => 'on_track',
        };

        $message = match ($status) {
            'setup_needed' => 'Set a monthly budget first so your month runway can guide daily spending.',
            'over_budget' => 'Spending is over your available budget; pause non-essentials and review recent transactions.',
            'no_daily_budget' => 'Your daily budget is used up; keep spending to essentials only.',
            'tight' => 'Budget runway is tight; keep daily spending small and predictable.',
            default => 'Your budget runway looks steady; keep pacing spending through month end.',
        };

        return [
            'days_left' => $daysLeft,
            'monthly_budget' => round($monthlyBudget, 2),
            'monthly_spent' => round($totalSpent, 2),
            'total_available' => round($totalAvailable, 2),
            'remaining' => round($remaining, 2),
            'daily_safe_spend' => round($dailySafeSpend, 2),
            'ptptn_enabled' => $ptptnEnabled,
            'ptptn_remaining' => round((float) data_get($ptptnMetrics, 'ptptn_remaining', 0), 2),
            'status' => $status,
            'message' => $message,
        ];
    }

    protected function budgetRunwayNote(AICategorizer $ai, array $budgetRunway, int $userId): string
    {
        $cacheKey = 'budget-runway-note:' . $userId . ':' . now()->format('Y-m-d-H') . ':' . md5(json_encode([
            $budgetRunway['status'],
            $budgetRunway['remaining'],
            $budgetRunway['daily_safe_spend'],
            $budgetRunway['ptptn_enabled'],
            $budgetRunway['ptptn_remaining'],
        ]));

        try {
            return Cache::remember(
                $cacheKey,
                now()->copy()->endOfHour(),
                fn () => $ai->getBudgetRunwayNote($budgetRunway, $userId)
            );
        } catch (Throwable) {
            return $ai->getBudgetRunwayNote($budgetRunway, $userId);
        }
    }

    protected function calculateHealthScore($user): int
    {
        $totalSpent = $this->monthlySpentThisMonth($user);

        $ptptnMetrics = (new PtptnMode())->metrics($user, (float) $totalSpent);
        $scoreBase = $user->ptptn_mode
            ? $ptptnMetrics['total_available']
            : (float) $user->monthly_allowance;

        if ($scoreBase == 0) {
            return 50;
        }

        $spendRatio = $totalSpent / $scoreBase;
        $score = 100 - ($spendRatio * 100);
        $score += min($user->saving_streak, 20);

        if ($user->ptptn_mode) {
            $ptptnStatus = $ptptnMetrics['status'];
            $score += $ptptnStatus === 'on_track' ? 5 : 0;
            $score -= in_array($ptptnStatus, ['over_budget', 'protect_reserve'], true) ? 10 : 0;
        }

        return (int) round(max(0, min(100, $score)));
    }

    protected function monthlySpentThisMonth(User $user): float
    {
        return (float) Transaction::where('user_id', $user->id)
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');
    }
}
