@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">Login</h1>

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
            <label for="login_email" class="block text-gray-700">Email</label>
            <input id="login_email" type="email" name="email" value="{{ old('email') }}" class="border rounded-lg p-2 w-full" required autofocus>
        </div>
        <div>
            <label for="login_password" class="block text-gray-700">Password</label>
            <input id="login_password" type="password" name="password" class="border rounded-lg p-2 w-full" required>
        </div>
        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input id="remember" type="checkbox" name="remember" value="1">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Forgot password?</a>
        </div>
        <button type="submit" class="bg-emerald-500 text-white rounded-lg px-4 py-2 w-full hover:bg-emerald-600">Login</button>
    </form>

    <p class="text-sm text-gray-500 mt-4">
        No account yet?
        <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700">Register</a>
    </p>
</div>
@endsection
