@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-3">Forgot your password?</h1>
    <p class="mb-5 text-sm leading-6 text-gray-600">
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
            <label for="forgot_email" class="block text-gray-700">Personal E-mail Address</label>
            <input
                id="forgot_email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your personal email address"
                class="border rounded-lg p-2 w-full"
                required
                autofocus
            >
        </div>
        <button type="submit" class="bg-emerald-500 text-white rounded-lg px-4 py-2 w-full hover:bg-emerald-600">Email Password Reset Link</button>
    </form>

    <p class="text-sm text-gray-500 mt-4">
        Remember your password?
        <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700">Login</a>
    </p>
</div>
@endsection
