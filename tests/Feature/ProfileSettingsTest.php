<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_does_not_show_budget_field(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertDontSee('Monthly allowance');
        $response->assertDontSee('monthly_allowance');
        $response->assertDontSee('Monthly budget');
    }

    public function test_profile_page_shows_account_budget_and_password_forms(): void
    {
        $user = User::factory()->create([
            'name' => 'Aina',
            'email' => 'aina@example.com',
            'campus' => 'KL',
            'monthly_allowance' => 100,
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Account & Budget', false);
        $response->assertSeeText('Monthly budget (RM)');
        $response->assertSeeText('Enable PTPTN Mode');
        $response->assertSee('name="ptptn_balance"', false);
        $response->assertSeeText('Change password');
        $response->assertSee(route('profile.password.update'), false);
        $response->assertSee('name="current_password"', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    public function test_dashboard_does_not_show_profile_settings_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertDontSeeText('Account & Budget');
        $response->assertDontSee('name="monthly_budget"', false);
    }

    public function test_user_can_update_profile_and_budget_from_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'campus' => null,
            'monthly_allowance' => 0,
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Aina Budget',
            'email' => 'aina@example.com',
            'campus' => 'KL',
            'monthly_budget' => 750.50,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success', 'Account, budget, and PTPTN settings updated.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Aina Budget',
            'email' => 'aina@example.com',
            'campus' => 'KL',
            'monthly_allowance' => 750.50,
            'ptptn_mode' => false,
            'ptptn_balance' => 0,
        ]);
    }

    public function test_user_can_enable_ptptn_mode_from_profile_page(): void
    {
        $user = User::factory()->create([
            'monthly_allowance' => 0,
            'ptptn_mode' => false,
            'ptptn_balance' => 0,
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'campus' => 'KL',
            'monthly_budget' => 500,
            'ptptn_mode' => '1',
            'ptptn_balance' => 1200,
        ]);

        $response->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'ptptn_mode' => true,
            'ptptn_balance' => 1200,
            'monthly_allowance' => 500,
        ]);
    }

    public function test_user_can_change_password_from_profile_page(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->patch('/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success', 'Password updated.');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }
}
