@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $budget = (float) $user->monthly_allowance;
    $remainingBalance = $ptptnMetrics['enabled'] ? $ptptnMetrics['remaining'] : $remaining;
    $spendingBase = $ptptnMetrics['enabled'] ? $ptptnMetrics['total_available'] : $budget;
    $spentPercent = $spendingBase > 0 ? min(100, max(0, ($totalSpent / $spendingBase) * 100)) : 0;
@endphp

<div class="space-y-6">
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="hf-kicker text-sm font-semibold uppercase tracking-wide">Dashboard</p>
            <h1 class="hf-title mt-1 text-3xl font-black tracking-tight">Welcome back, {{ $user->name }}</h1>
            <p class="hf-muted mt-2">
                {{ $dashboardIntro }}
            </p>
        </div>
        <a href="{{ route('scan-receipt.form') }}" class="hf-btn inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold">
            Scan receipt
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="hf-card rounded-lg p-5">
            <p class="text-sm text-gray-500">Monthly budget</p>
            <p class="mt-2 text-2xl font-bold text-gray-950">RM {{ number_format($budget, 2) }}</p>
        </div>
        <div class="hf-card rounded-lg p-5">
            <p class="text-sm text-gray-500">Spent this month</p>
            <p class="mt-2 text-2xl font-bold text-rose-600">RM {{ number_format($totalSpent, 2) }}</p>
        </div>
        <div class="hf-card rounded-lg p-5">
            <p class="text-sm text-gray-500">{{ $ptptnMetrics['enabled'] ? 'Remaining balance' : 'Remaining' }}</p>
            <p class="mt-2 text-2xl font-bold {{ $remainingBalance < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                RM {{ number_format($remainingBalance, 2) }}
            </p>

        </div>
        <div class="hf-card rounded-lg p-5">
            <p class="text-sm text-gray-500">Saving streak</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $user->saving_streak }} {{ $user->saving_streak === 1 ? 'day' : 'days' }}</p>
        </div>
    </div>

    @if($ptptnMetrics['enabled'])
        <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="hf-soft-card h-full rounded-lg p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">PTPTN Mode</p>
                        <h2 class="mt-1 text-xl font-bold text-gray-950">Loan-aware spending guardrail</h2>
                        <p class="mt-2 text-sm text-emerald-900">{{ $ptptnMetrics['message'] }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="hf-btn-secondary inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold">
                        Tune PTPTN mode
                    </a>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Safe daily spend</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">RM {{ number_format($ptptnMetrics['daily_safe_spend'], 2) }}</p>

                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Monthly budget left</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">RM {{ number_format($ptptnMetrics['monthly_budget_remaining'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">PTPTN used</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">RM {{ number_format($ptptnMetrics['ptptn_used'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Spendable now</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">RM {{ number_format($ptptnMetrics['spendable_after_reserve'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">PTPTN left</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">RM {{ number_format($ptptnMetrics['ptptn_remaining'], 2) }}</p>
                    </div>
                </div>
            </div>

            @include('partials.month-runway-card')
        </div>

    @else
        <div class="grid grid-cols-1 items-stretch gap-4 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="hf-card h-full rounded-lg p-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Budget runway</p>
                <h2 class="mt-1 text-xl font-bold text-gray-950">Monthly spending pace</h2>
                <p class="mt-2 text-sm text-gray-600">{{ $budgetRunway['message'] }}</p>

                <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Safe daily spend</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">RM {{ number_format($budgetRunway['daily_safe_spend'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Budget left</p>
                        <p class="mt-1 text-2xl font-bold {{ $budgetRunway['remaining'] < 0 ? 'text-rose-600' : 'text-gray-950' }}">RM {{ number_format($budgetRunway['remaining'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Spent this month</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">RM {{ number_format($budgetRunway['monthly_spent'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Days left</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950">{{ $budgetRunway['days_left'] }}</p>
                    </div>
                </div>
            </div>

            @include('partials.month-runway-card')
        </div>
    @endif

    <div class="hf-card rounded-lg p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-950">Budget health</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $insight }}</p>
            </div>
        </div>
        <div class="mt-5 h-3 overflow-hidden rounded-full bg-gray-100">
            <div class="h-full rounded-full {{ $spentPercent > 90 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $spentPercent }}%"></div>
        </div>
        <div class="mt-2 flex justify-between text-xs text-gray-500">
            <span></span>
            <span>{{ number_format($spentPercent, 0) }}% {{ $ptptnMetrics['enabled'] ? 'total funds used' : 'used' }}</span>
        </div>
    </div>

    <div class="hf-card rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-950">Add transaction</h2>
        <form action="{{ route('transaction.store') }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
            @csrf
            <label for="transaction_description" class="sr-only">Description</label>
            <input id="transaction_description" type="text" name="description" placeholder="e.g. Nasi Lemak" class="hf-input" required>
            <label for="transaction_amount" class="sr-only">Amount (RM)</label>
            <input id="transaction_amount" type="number" step="0.01" name="amount" placeholder="Amount (RM)" class="hf-input" required>
            <label for="transaction_category" class="sr-only">Category</label>
            <select id="transaction_category" name="category" class="hf-input" required>
                <option value="">Select category</option>
                @foreach($transactionCategories as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <label for="transaction_date" class="sr-only">Transaction date</label>
            <input id="transaction_date" type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="hf-input" required>
            <button type="submit" class="hf-btn rounded-lg px-4 py-2 text-sm font-semibold md:col-span-4">Add transaction</button>
        </form>
        <div class="mt-4 flex justify-end">
            <a href="{{ route('transactions') }}" class="hf-link text-sm font-medium">Search transactions</a>
        </div>
    </div>

    <div class="hf-card overflow-hidden rounded-lg">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-950">Recent transactions</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Description</th>
                        <th class="px-6 py-3">Amount</th>
                        <th class="px-6 py-3">Category</th>
                        <th class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $transaction->description }}</td>
                            <td class="px-6 py-4 {{ $transaction->amount > 50 ? 'text-rose-600' : 'text-gray-700' }}">RM {{ number_format($transaction->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ $transaction->category }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $transaction->transaction_date->format('d M') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
