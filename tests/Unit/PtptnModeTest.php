<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\PtptnMode;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PtptnModeTest extends TestCase
{
    public function test_it_calculates_safe_daily_spend_after_ptptn_reserve(): void
    {
        $user = new User([
            'monthly_allowance' => 500,
            'ptptn_balance' => 1000,
            'ptptn_mode' => true,
        ]);

        $metrics = (new PtptnMode())->metrics($user, 100, Carbon::parse('2026-05-22'));

        $this->assertTrue($metrics['enabled']);
        $this->assertSame(1500.0, $metrics['total_available']);
        $this->assertSame(1400.0, $metrics['remaining']);
        $this->assertSame(400.0, $metrics['monthly_budget_remaining']);
        $this->assertSame(0.0, $metrics['ptptn_used']);
        $this->assertSame(50.0, $metrics['recommended_reserve']);
        $this->assertSame(1350.0, $metrics['spendable_after_reserve']);
        $this->assertSame(135.0, $metrics['daily_safe_spend']);
        $this->assertSame('on_track', $metrics['status']);
    }

    public function test_it_cuts_into_ptptn_after_monthly_budget_is_exceeded(): void
    {
        $user = new User([
            'monthly_allowance' => 10,
            'ptptn_balance' => 10000,
            'ptptn_mode' => true,
        ]);

        $metrics = (new PtptnMode())->metrics($user, 4623.98, Carbon::parse('2026-05-23'));

        $this->assertSame(10010.0, $metrics['total_available']);
        $this->assertSame(5386.02, $metrics['remaining']);
        $this->assertSame(0.0, $metrics['monthly_budget_remaining']);
        $this->assertSame(4613.98, $metrics['ptptn_used']);
        $this->assertSame(5386.02, $metrics['ptptn_remaining']);
        $this->assertSame(598.34, $metrics['daily_safe_spend']);
        $this->assertSame('using_ptptn', $metrics['status']);
    }

    public function test_it_flags_a_purchase_that_would_enter_the_reserve(): void
    {
        $user = new User([
            'monthly_allowance' => 500,
            'ptptn_balance' => 1000,
            'ptptn_mode' => true,
        ]);

        $decision = (new PtptnMode())->affordability($user, 'headphones', 60, 1400);

        $this->assertSame('Technically yes, but PTPTN Mode says pause on headphones.', $decision['answer']);
        $this->assertStringContainsString('below your PTPTN reserve', $decision['advice']);
    }
}
