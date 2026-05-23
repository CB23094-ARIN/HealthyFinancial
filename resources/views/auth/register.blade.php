@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">Register</h1>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-gray-700">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" class="border rounded-lg p-2 w-full" required autofocus>
        </div>
        <div>
            <label class="block text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="border rounded-lg p-2 w-full" required>
        </div>
        <div>
            <label class="block text-gray-700">University name</label>
            <input type="text" name="university_name" value="{{ old('university_name') }}" class="border rounded-lg p-2 w-full">
        </div>
        <div>
            <label class="block text-gray-700">Password</label>
            <input type="password" name="password" class="border rounded-lg p-2 w-full" required>
        </div>
        <div>
            <label class="block text-gray-700">Confirm password</label>
            <input type="password" name="password_confirmation" class="border rounded-lg p-2 w-full" required>
        </div>
        <button type="submit" class="bg-emerald-500 text-white rounded-lg px-4 py-2 w-full hover:bg-emerald-600">Register</button>
    </form>

    <p class="text-sm text-gray-500 mt-4">
        Already have an account?
        <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700">Login</a>
    </p>
</div>
@endsection
