<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class PtptnMode
{
    public function metrics(User $user, float $monthlySpent, ?CarbonInterface $today = null): array
    {
        $today = $today ? Carbon::parse($today) : now();
        $monthlyBudget = (float) $user->monthly_allowance;
        $ptptnBalance = (float) $user->ptptn_balance;
        $totalAvailable = $user->ptptn_mode
            ? $monthlyBudget + $ptptnBalance
            : $monthlyBudget;
        $remaining = $totalAvailable - $monthlySpent;
        $monthlyBudgetRemaining = max(0, $monthlyBudget - $monthlySpent);
        $monthlyBudgetOverage = max(0, $monthlySpent - $monthlyBudget);
        $ptptnUsed = $user->ptptn_mode
            ? min($ptptnBalance, $monthlyBudgetOverage)
            : 0.0;
        $ptptnRemaining = max(0, $ptptnBalance - $ptptnUsed);
        $daysLeft = max(1, (int) $today->daysInMonth - (int) $today->day + 1);
        $reserve = $user->ptptn_mode
            ? $this->recommendedReserve($monthlyBudget, $ptptnRemaining)
            : 0.0;

        $spendableAfterReserve = max(0, $remaining - $reserve);
        $dailySafeSpend = $spendableAfterReserve / $daysLeft;

        return [
            'enabled' => (bool) $user->ptptn_mode,
            'monthly_budget' => round($monthlyBudget, 2),
            'monthly_spent' => round($monthlySpent, 2),
            'monthly_budget_remaining' => round($monthlyBudgetRemaining, 2),
            'monthly_budget_overage' => round($monthlyBudgetOverage, 2),
            'total_available' => round($totalAvailable, 2),
            'remaining' => round($remaining, 2),
            'days_left' => $daysLeft,
            'ptptn_balance' => round($ptptnBalance, 2),
            'ptptn_used' => round($ptptnUsed, 2),
            'ptptn_remaining' => round($ptptnRemaining, 2),
            'recommended_reserve' => round($reserve, 2),
            'spendable_after_reserve' => round($spendableAfterReserve, 2),
            'daily_safe_spend' => round($dailySafeSpend, 2),
            'reserve_gap' => round(max(0, $reserve - $remaining), 2),
            'status' => $this->status($totalAvailable, $remaining, $reserve, $dailySafeSpend, $monthlyBudgetOverage),
            'message' => $this->message($totalAvailable, $remaining, $reserve, $dailySafeSpend, $monthlyBudgetOverage, $ptptnUsed),
        ];
    }

    public function affordability(User $user, string $itemName, float $price, float $monthlySpent): ?array
    {
        if (! $user->ptptn_mode) {
            return null;
        }

        $metrics = $this->metrics($user, $monthlySpent);
        $remainingAfterPurchase = $metrics['remaining'] - $price;
        $reserveGapAfterPurchase = max(0, $metrics['recommended_reserve'] - $remainingAfterPurchase);

        if ($price <= $metrics['spendable_after_reserve']) {
            return [
                'answer' => "Yes, PTPTN Mode approves {$itemName}.",
                'advice' => 'After buying it, you still protect your RM' . number_format($metrics['recommended_reserve'], 2) . ' PTPTN reserve and have RM' . number_format($remainingAfterPurchase, 2) . ' total balance left, including PTPTN.',
                'metrics' => $metrics,
            ];
        }

        if ($price <= $metrics['remaining']) {
            return [
                'answer' => "Technically yes, but PTPTN Mode says pause on {$itemName}.",
                'advice' => 'It would leave RM' . number_format($remainingAfterPurchase, 2) . ', which is below your PTPTN reserve. Save RM' . number_format($reserveGapAfterPurchase, 2) . ' more first or choose a cheaper option.',
                'metrics' => $metrics,
            ];
        }

        return null;
    }

    public function leaderboardBonus(User $user, float $monthlySpent): int
    {
        if (! $user->ptptn_mode) {
            return 0;
        }

        $status = $this->metrics($user, $monthlySpent)['status'];

        return match ($status) {
            'on_track' => 20,
            'tight', 'using_ptptn' => 10,
            default => 0,
        };
    }

    protected function recommendedReserve(float $monthlyBudget, float $ptptnBalance): float
    {
        if ($monthlyBudget <= 0 || $ptptnBalance <= 0) {
            return 0.0;
        }

        $tenPercent = $monthlyBudget * 0.1;
        $floor = $monthlyBudget >= 300 ? 30 : $tenPercent;

        return min($ptptnBalance, max($floor, $tenPercent));
    }

    protected function status(float $totalAvailable, float $remaining, float $reserve, float $dailySafeSpend, float $monthlyBudgetOverage): string
    {
        if ($totalAvailable <= 0) {
            return 'setup_needed';
        }

        if ($remaining < 0) {
            return 'over_budget';
        }

        if ($remaining < $reserve) {
            return 'protect_reserve';
        }

        if ($monthlyBudgetOverage > 0) {
            return 'using_ptptn';
        }

        if ($dailySafeSpend < 10) {
            return 'tight';
        }

        return 'on_track';
    }

    protected function message(float $totalAvailable, float $remaining, float $reserve, float $dailySafeSpend, float $monthlyBudgetOverage, float $ptptnUsed): string
    {
        if ($totalAvailable <= 0) {
            return 'Set a monthly budget or PTPTN balance first so PTPTN Mode can calculate your safe daily spend.';
        }

        if ($remaining < 0) {
            return 'PTPTN alert: spending has used up the monthly budget and PTPTN balance, so pause non-essential spending.';
        }

        if ($remaining < $reserve) {
            return 'Rebuild your PTPTN reserve before buying non-essentials.';
        }

        if ($monthlyBudgetOverage > 0) {
            return 'Monthly budget is fully used; RM' . number_format($ptptnUsed, 2) . ' is now coming from PTPTN. You still have RM' . number_format($remaining, 2) . ' total balance left.';
        }

        if ($dailySafeSpend < 10) {
            return 'PTPTN Mode is tight this month; keep daily spending small and predictable.';
        }

        return 'On track: your daily spend and PTPTN reserve are both protected.';
    }
}
