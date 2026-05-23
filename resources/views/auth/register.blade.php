@extends('layouts.app')

@section('content')
<div class="hf-card mx-auto max-w-md rounded-2xl p-8">
    <img src="{{ asset('images/healthyfinancial-logo.png') }}" alt="HealthyFinancial" class="hf-auth-logo">

    <h1 class="hf-title mb-2 text-3xl font-black tracking-tight">Register</h1>
    <p class="hf-muted mb-6 text-sm">Create your HealthyFinancial account and start tracking smarter.</p>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label for="register_name" class="hf-label-text">Name</label>
            <input id="register_name" type="text" name="name" value="{{ old('name') }}" class="hf-input mt-1" required autofocus>
        </div>
        <div>
            <label for="register_email" class="hf-label-text">Email</label>
            <input id="register_email" type="email" name="email" value="{{ old('email') }}" class="hf-input mt-1" required>
        </div>
        <div>
            <label for="register_university_name" class="hf-label-text">University name</label>
            <input id="register_university_name" type="text" name="university_name" value="{{ old('university_name') }}" class="hf-input mt-1">
        </div>
        <div>
            <label for="register_password" class="hf-label-text">Password</label>
            <input id="register_password" type="password" name="password" class="hf-input mt-1" required>
        </div>
        <div>
            <label for="register_password_confirmation" class="hf-label-text">Confirm password</label>
            <input id="register_password_confirmation" type="password" name="password_confirmation" class="hf-input mt-1" required>
        </div>
        <button type="submit" class="hf-btn w-full rounded-lg px-4 py-2.5 font-semibold">Register</button>
    </form>

    <p class="hf-muted mt-5 text-sm">
        Already have an account?
        <a href="{{ route('login') }}" class="hf-link font-medium">Login</a>
    </p>
</div>
@endsection
