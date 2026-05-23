@extends('layouts.app')

@section('content')
<div class="hf-card mx-auto max-w-md rounded-2xl p-8">
    <img src="{{ asset('images/healthyfinancial-logo.png') }}" alt="HealthyFinancial" class="hf-auth-logo">

    <h1 class="hf-title mb-2 text-3xl font-black tracking-tight">Login</h1>
    <p class="hf-muted mb-6 text-sm">Welcome back. Keep your spending calm and clear.</p>

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

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="login_email" class="hf-label-text">Email</label>
            <input id="login_email" type="email" name="email" value="{{ old('email') }}" class="hf-input mt-1" required autofocus>
        </div>
        <div>
            <label for="login_password" class="hf-label-text">Password</label>
            <input id="login_password" type="password" name="password" class="hf-input mt-1" required>
        </div>
        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input id="remember" type="checkbox" name="remember" value="1">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="hf-link text-sm font-medium">Forgot password?</a>
        </div>
        <button type="submit" class="hf-btn w-full rounded-lg px-4 py-2.5 font-semibold">Login</button>
    </form>

    <p class="hf-muted mt-5 text-sm">
        No account yet?
        <a href="{{ route('register') }}" class="hf-link font-medium">Register</a>
    </p>
</div>
@endsection
