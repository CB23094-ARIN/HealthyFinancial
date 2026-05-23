<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_saving_streak_counts_unique_activity_days(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $user = User::factory()->create([
            'monthly_allowance' => 500,
            'saving_streak' => 0,
        ]);

        $this->actingAs($user)->post('/transaction', [
            'description' => 'Nasi Lemak',
            'amount' => 5.50,
            'category' => 'Food',
            'transaction_date' => '2026-05-22',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(1, $user->refresh()->saving_streak);

        $this->actingAs($user)->post('/transaction', [
            'description' => 'Teh O',
            'amount' => 2.00,
            'category' => 'Beverages',
            'transaction_date' => '2026-05-22',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(1, $user->refresh()->saving_streak);

        $this->actingAs($user)->post('/transaction', [
            'description' => 'Bus',
            'amount' => 1.50,
            'category' => 'Transportation expenses',
            'transaction_date' => '2026-05-21',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(2, $user->refresh()->saving_streak);
        $this->assertDatabaseHas('leaderboard', [
            'user_id' => $user->id,
            'points' => 23,
        ]);
    }

    public function test_dashboard_transaction_form_has_category_dropdown(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('name="category"', false);
        $response->assertSeeText('Food');
        $response->assertSeeText('Beverages');
        $response->assertSeeText('Living expenses');
        $response->assertSeeText('Savings and investments');
        $response->assertSeeText('Search transactions');
    }

    public function test_dashboard_shows_ptptn_mode_guardrail_when_enabled(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $user = User::factory()->create([
            'monthly_allowance' => 500,
            'ptptn_mode' => true,
            'ptptn_balance' => 1000,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Groceries',
            'amount' => 100,
            'category' => 'Living expenses',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('PTPTN Mode');
        $response->assertSeeText('Loan-aware spending guardrail');
        $response->assertSeeText('RM 135.00');
        $response->assertSeeText('PTPTN used');
        $response->assertSeeText('Monthly budget left');
        $response->assertSeeText('AI note:');
        $response->assertDontSeeText('Tiny reminder: pace your spending so PTPTN lasts comfortably.');
        $response->assertDontSeeText('PTPTN Mode is watching your safe daily spend, reserve, and monthly runway.');
    }

    public function test_dashboard_includes_ptptn_in_remaining_balance_after_budget_is_exceeded(): void
    {
        Carbon::setTestNow('2026-05-23 10:00:00');

        $user = User::factory()->create([
            'monthly_allowance' => 10,
            'ptptn_mode' => true,
            'ptptn_balance' => 10000,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Semester supplies',
            'amount' => 4623.98,
            'category' => 'Education',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('Remaining balance');
        $response->assertSeeText('RM 5,386.02');
        $response->assertSeeText('RM 4,613.98');
        $response->assertSeeText('is now coming from PTPTN');
    }

    public function test_transactions_can_be_searched_and_filtered(): void
    {
        $user = User::factory()->create();

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Coffee',
            'amount' => 8,
            'category' => 'Beverages',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Phone bill',
            'amount' => 90,
            'category' => 'Technology',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->get('/transactions?search=Coffee&category=Beverages');

        $response->assertOk();
        $response->assertSeeText('Coffee');
        $response->assertDontSeeText('Phone bill');
        $response->assertSeeText('Search');
        $response->assertSeeText('Clear');
    }

    public function test_can_afford_uses_plain_safe_text(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $user = User::factory()->create([
            'monthly_allowance' => 100,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Accidental large entry',
            'amount' => 100000,
            'category' => 'other',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->post('/can-afford', [
            'item_name' => 'shoes',
            'item_price' => 1000,
        ]);

        $response->assertOk();
        $response->assertSeeText('Be careful. shoes would put you over budget by RM100,900.00.');
        $response->assertSeeText('You are already over budget. Review recent large transactions before saving for this.');
        $response->assertDontSee('**', false);
    }

    public function test_ptptn_mode_can_afford_protects_reserve(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $user = User::factory()->create([
            'monthly_allowance' => 500,
            'ptptn_mode' => true,
            'ptptn_balance' => 1000,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Rent',
            'amount' => 1400,
            'category' => 'Living expenses',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->post('/can-afford', [
            'item_name' => 'headphones',
            'item_price' => 60,
        ]);

        $response->assertOk();
        $response->assertSeeText('Technically yes, but PTPTN Mode says pause on headphones.');
        $response->assertSeeText('below your PTPTN reserve');
        $response->assertSeeText('AI note:');
    }

    public function test_scan_receipt_page_supports_camera_capture(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/scan-receipt');

        $response->assertOk();
        $response->assertSee('capture="environment"', false);
        $response->assertSee('id="startCamera"', false);
        $response->assertSee('getUserMedia', false);
    }

    public function test_dashboard_refreshes_stale_saving_streak_by_day(): void
    {
        Carbon::setTestNow('2026-05-23 10:00:00');

        $user = User::factory()->create([
            'saving_streak' => 99,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Old lunch',
            'amount' => 8,
            'category' => 'Food',
            'transaction_date' => '2026-05-20',
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('0 days');
        $this->assertSame(0, $user->refresh()->saving_streak);
    }

    public function test_leaderboard_syncs_from_existing_transactions(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $user = User::factory()->create([
            'name' => 'Aina',
            'university_name' => 'UMPSA',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Lunch',
            'amount' => 8,
            'category' => 'food',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->get('/leaderboard');

        $response->assertOk();
        $response->assertSeeText('Aina');
        $this->assertDatabaseHas('leaderboard', [
            'user_id' => $user->id,
            'university_name' => 'UMPSA',
            'points' => 11,
        ]);
    }

    public function test_ptptn_mode_adds_leaderboard_bonus_when_on_track(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $user = User::factory()->create([
            'name' => 'Aina',
            'university_name' => 'UMPSA',
            'monthly_allowance' => 500,
            'ptptn_mode' => true,
            'ptptn_balance' => 1000,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'description' => 'Lunch',
            'amount' => 8,
            'category' => 'Food',
            'transaction_date' => now(),
            'is_auto_categorized' => false,
        ]);

        $response = $this->actingAs($user)->get('/leaderboard');

        $response->assertOk();
        $response->assertSeeText('PTPTN');
        $this->assertDatabaseHas('leaderboard', [
            'user_id' => $user->id,
            'points' => 31,
        ]);
    }
}
