@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">Reset password</h1>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="reset_email" class="block text-gray-700">Email</label>
            <input id="reset_email" type="email" name="email" value="{{ old('email', $email) }}" class="border rounded-lg p-2 w-full" required autofocus>
        </div>

        <div>
            <label for="reset_password" class="block text-gray-700">New password</label>
            <input id="reset_password" type="password" name="password" class="border rounded-lg p-2 w-full" required>
        </div>

        <div>
            <label for="reset_password_confirmation" class="block text-gray-700">Confirm new password</label>
            <input id="reset_password_confirmation" type="password" name="password_confirmation" class="border rounded-lg p-2 w-full" required>
        </div>

        <button type="submit" class="bg-emerald-500 text-white rounded-lg px-4 py-2 w-full hover:bg-emerald-600">Reset password</button>
    </form>
</div>
@endsection
