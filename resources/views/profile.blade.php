@extends('layouts.app')

@section('content')
@php
    $budget = (float) $user->monthly_allowance;
    $ptptnBalance = (float) $user->ptptn_balance;
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
                <label for="profile_name" class="text-sm font-medium text-gray-700">Name</label>
                <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label for="profile_email" class="text-sm font-medium text-gray-700">Email</label>
                <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label for="profile_university_name" class="text-sm font-medium text-gray-700">University name</label>
                <input id="profile_university_name" type="text" name="university_name" value="{{ old('university_name', $user->university_name) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>

            <div>
                <label for="profile_monthly_budget" class="text-sm font-medium text-gray-700">Monthly budget (RM)</label>
                <input id="profile_monthly_budget" type="number" step="0.01" min="0" name="monthly_budget" value="{{ old('monthly_budget', $budget) }}" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                <label class="flex items-start gap-3">
                    <input id="profile_ptptn_mode" type="checkbox" name="ptptn_mode" value="1" class="mt-1 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500" @checked(old('ptptn_mode', $user->ptptn_mode))>
                    <span>
                        <span class="block text-sm font-semibold text-gray-950">Enable PTPTN Mode</span>
                        <span class="mt-1 block text-sm text-emerald-900">Turn your monthly budget into a loan-aware spending plan with a safe daily spend, protected reserve, and leaderboard bonus.</span>
                    </span>
                </label>

                <div class="mt-4">
                    <label for="profile_ptptn_balance" class="text-sm font-medium text-gray-700">PTPTN balance or target to protect (RM)</label>
                    <input id="profile_ptptn_balance" type="number" step="0.01" min="0" name="ptptn_balance" value="{{ old('ptptn_balance', $ptptnBalance) }}" class="mt-1 w-full rounded-lg border border-emerald-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                </div>
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
                <label for="current_password" class="text-sm font-medium text-gray-700">Current password</label>
                <input id="current_password" type="password" name="current_password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label for="new_password" class="text-sm font-medium text-gray-700">New password</label>
                <input id="new_password" type="password" name="password" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <div>
                <label for="new_password_confirmation" class="text-sm font-medium text-gray-700">Confirm new password</label>
                <input id="new_password_confirmation" type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            </div>

            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Update password</button>
        </form>
    </div>
</div>
@endsection
