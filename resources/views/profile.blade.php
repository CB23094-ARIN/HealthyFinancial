@extends('layouts.app')

@section('content')
@php
    $budget = (float) $user->monthly_allowance;
@endphp

<div class="mx-auto max-w-xl space-y-6">
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h1 class="text-lg font-semibold text-gray-950">Account & Budget</h1>

        <form action="{{ route('profile.update') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Campus</label>
                <input type="text" name="campus" value="{{ old('campus', $user->campus) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Monthly budget (RM)</label>
                <input type="number" step="0.01" min="0" name="monthly_budget" value="{{ old('monthly_budget', $budget) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <button type="submit" class="w-full rounded-lg bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Save changes</button>
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-950">Change password</h2>

        <form action="{{ route('profile.password.update') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="text-sm font-medium text-gray-700">Current password</label>
                <input type="password" name="current_password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">New password</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Confirm new password</label>
                <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Update password</button>
        </form>
    </div>
</div>
@endsection
