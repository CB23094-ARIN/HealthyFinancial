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

    public function test_scan_receipt_page_supports_camera_capture(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/scan-receipt');

        $response->assertOk();
        $response->assertSee('capture="environment"', false);
        $response->assertSee('id="startCamera"', false);
        $response->assertSee('getUserMedia', false);
    }

    public function test_leaderboard_syncs_from_existing_transactions(): void
    {
        Carbon::setTestNow('2026-05-22 10:00:00');

        $user = User::factory()->create([
            'name' => 'Aina',
            'campus' => 'KL',
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
            'campus' => 'KL',
            'points' => 11,
        ]);
    }
}
