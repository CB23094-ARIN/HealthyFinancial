<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_links_to_forgot_password(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSeeText('Forgot password?');
        $response->assertSee(route('password.request'), false);
    }

    public function test_forgot_password_page_uses_personal_email_copy(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSeeText('Forgot your password?');
        $response->assertSeeText('No problem. Just enter your personal email address');
        $response->assertSeeText('Personal E-mail Address');
        $response->assertSee('placeholder="Enter your personal email address"', false);
        $response->assertSeeText('Email Password Reset Link');
    }

    public function test_user_can_request_password_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'asd@asd.com',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => 'asd@asd.com',
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_request_handles_mail_transport_failure(): void
    {
        User::factory()->create([
            'email' => 'asd@asd.com',
        ]);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->andThrow(new TransportException('Bad SMTP credentials'));

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'asd@asd.com',
        ]);

        $response->assertRedirect('/forgot-password');
        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'asd@asd.com',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'asd@asd.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }
}
