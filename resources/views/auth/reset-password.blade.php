@extends('layouts.app')

@section('content')
<div class="hf-card mx-auto max-w-md rounded-2xl p-8">
    <h1 class="hf-title mb-2 text-3xl font-black tracking-tight">Reset password</h1>
    <p class="hf-muted mb-6 text-sm">Choose a new password to get back into your account.</p>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="reset_email" class="hf-label-text">Email</label>
            <input id="reset_email" type="email" name="email" value="{{ old('email', $email) }}" class="hf-input mt-1" required autofocus>
        </div>

        <div>
            <label for="reset_password" class="hf-label-text">New password</label>
            <input id="reset_password" type="password" name="password" class="hf-input mt-1" required>
        </div>

        <div>
            <label for="reset_password_confirmation" class="hf-label-text">Confirm new password</label>
            <input id="reset_password_confirmation" type="password" name="password_confirmation" class="hf-input mt-1" required>
        </div>

        <button type="submit" class="hf-btn w-full rounded-lg px-4 py-2.5 font-semibold">Reset password</button>
    </form>
</div>
@endsection
