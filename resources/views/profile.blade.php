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

    <div class="hf-card rounded-lg p-6">
        <h1 class="hf-title text-lg font-bold">Account & Budget</h1>

        <form action="{{ route('profile.update') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="profile_name" class="hf-label-text">Name</label>
                <input id="profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" class="hf-input mt-1" required>
            </div>

            <div>
                <label for="profile_email" class="hf-label-text">Email</label>
                <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}" class="hf-input mt-1" required>
            </div>

            <div>
                <label for="profile_university_name" class="hf-label-text">University name</label>
                <input id="profile_university_name" type="text" name="university_name" value="{{ old('university_name', $user->university_name) }}" class="hf-input mt-1">
            </div>

            <div>
                <label for="profile_monthly_budget" class="hf-label-text">Monthly budget (RM)</label>
                <input id="profile_monthly_budget" type="number" step="0.01" min="0" name="monthly_budget" value="{{ old('monthly_budget', $budget) }}" class="hf-input mt-1" required>
            </div>

            <div class="hf-note rounded-lg p-4">
                <label class="flex items-start gap-3">
                    <input id="profile_ptptn_mode" type="checkbox" name="ptptn_mode" value="1" class="mt-1 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500" @checked(old('ptptn_mode', $user->ptptn_mode))>
                    <span>
                        <span class="block text-sm font-semibold text-gray-950">Enable PTPTN Mode</span>
                        <span class="mt-1 block text-sm text-emerald-900">Turn your monthly budget into a loan-aware spending plan with a safe daily spend, protected reserve, and leaderboard bonus.</span>
                    </span>
                </label>

                <div class="mt-4">
                    <label for="profile_ptptn_balance" class="hf-label-text">PTPTN balance or target to protect (RM)</label>
                    <input id="profile_ptptn_balance" type="number" step="0.01" min="0" name="ptptn_balance" value="{{ old('ptptn_balance', $ptptnBalance) }}" class="hf-input mt-1">
                </div>
            </div>

            <button type="submit" class="hf-btn w-full rounded-lg px-4 py-2 text-sm font-semibold">Save changes</button>
        </form>
    </div>

    <div class="hf-card rounded-lg p-6">
        <h2 class="hf-title text-lg font-bold">Change password</h2>

        <form action="{{ route('profile.password.update') }}" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="current_password" class="hf-label-text">Current password</label>
                <input id="current_password" type="password" name="current_password" class="hf-input mt-1" required>
            </div>

            <div>
                <label for="new_password" class="hf-label-text">New password</label>
                <input id="new_password" type="password" name="password" class="hf-input mt-1" required>
            </div>

            <div>
                <label for="new_password_confirmation" class="hf-label-text">Confirm new password</label>
                <input id="new_password_confirmation" type="password" name="password_confirmation" class="hf-input mt-1" required>
            </div>

            <button type="submit" class="hf-btn w-full rounded-lg px-4 py-2 text-sm font-semibold">Update password</button>
        </form>
    </div>
</div>
@endsection
