@extends('layouts.app')

@section('content')
<div class="hf-card mx-auto max-w-md rounded-2xl p-8">
    <h1 class="hf-title mb-3 text-3xl font-black tracking-tight">Forgot your password?</h1>
    <p class="hf-muted mb-6 text-sm leading-6">
        No problem. Just enter your personal email address, and we will email you a password reset link that will allow you to choose a new one.
    </p>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('status'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-4 rounded">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <div>
            <label for="forgot_email" class="hf-label-text">Personal E-mail Address</label>
            <input
                id="forgot_email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your personal email address"
                class="hf-input mt-1"
                required
                autofocus
            >
        </div>
        <button type="submit" class="hf-btn w-full rounded-lg px-4 py-2.5 font-semibold">Email Password Reset Link</button>
    </form>

    <p class="hf-muted mt-5 text-sm">
        Remember your password?
        <a href="{{ route('login') }}" class="hf-link font-medium">Login</a>
    </p>
</div>
@endsection
