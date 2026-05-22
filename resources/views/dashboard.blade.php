@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $budget = (float) $user->monthly_allowance;
    $spentPercent = $budget > 0 ? min(100, max(0, ($totalSpent / $budget) * 100)) : 0;
@endphp

<div class="space-y-6">
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-wide text-emerald-700">Dashboard</p>
            <h1 class="mt-1 text-3xl font-bold text-gray-950">Welcome back, {{ $user->name }}</h1>
            <p class="mt-2 text-gray-500">Track spending, update your budget, and keep your profile current.</p>
        </div>
        <a href="{{ route('scan-receipt.form') }}" class="inline-flex items-center justify-center rounded-lg bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
            Scan receipt
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Monthly budget</p>
            <p class="mt-2 text-2xl font-bold text-gray-950">RM {{ number_format($budget, 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Spent this month</p>
            <p class="mt-2 text-2xl font-bold text-rose-600">RM {{ number_format($totalSpent, 2) }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Remaining</p>
            <p class="mt-2 text-2xl font-bold {{ $remaining < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                RM {{ number_format($remaining, 2) }}
            </p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Saving streak</p>
            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $user->saving_streak }} days</p>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-950">Budget health</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $insight }}</p>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">{{ $healthScore }}/100</span>
        </div>
        <div class="mt-5 h-3 overflow-hidden rounded-full bg-gray-100">
            <div class="h-full rounded-full {{ $spentPercent > 90 ? 'bg-rose-500' : 'bg-emerald-500' }}" style="width: {{ $spentPercent }}%"></div>
        </div>
        <div class="mt-2 flex justify-between text-xs text-gray-500">
            <span>RM 0</span>
            <span>{{ number_format($spentPercent, 0) }}% used</span>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-950">Add transaction</h2>
        <form action="{{ route('transaction.store') }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4">
            @csrf
            <input type="text" name="description" placeholder="e.g. Nasi Lemak" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            <input type="number" step="0.01" name="amount" placeholder="Amount (RM)" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            <select name="category" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
                <option value="">Select category</option>
                @foreach($transactionCategories as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
            <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100" required>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 md:col-span-4">Add transaction</button>
        </form>
        <div class="mt-4 flex justify-end">
            <a href="{{ route('transactions') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">Search transactions</a>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
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
