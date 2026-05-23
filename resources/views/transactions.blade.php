@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="hf-kicker text-sm font-semibold uppercase tracking-wide">Transactions</p>
            <h1 class="hf-title mt-1 text-3xl font-black tracking-tight">All transactions</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="hf-btn inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold">
            Add transaction
        </a>
    </div>

    <div class="hf-card rounded-lg p-6">
        <form method="GET" action="{{ route('transactions') }}" class="grid grid-cols-1 items-end gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)_8rem_8rem]">
            <div>
                <label for="transaction_search" class="hf-label-text">Search description or category</label>
                <input id="transaction_search" type="search" name="search" value="{{ $search }}" placeholder="Search description or category" class="hf-input mt-1 h-11">
            </div>

            <div>
                <label for="transaction_filter_category" class="hf-label-text">Filter by category</label>
                <select id="transaction_filter_category" name="category" class="hf-input mt-1 h-11">
                    <option value="">All categories</option>
                    @foreach($transactionCategories as $category)
                        <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="hf-btn h-11 rounded-lg px-4 text-sm font-semibold">Search</button>

            @if($search || $selectedCategory)
                <a href="{{ route('transactions') }}" class="hf-btn-secondary inline-flex h-11 items-center justify-center rounded-lg px-4 text-sm font-semibold">
                    Clear
                </a>
            @else
                <span class="hidden h-11 lg:block"></span>
            @endif
        </form>
    </div>

    <div class="hf-card overflow-hidden rounded-lg">
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
                            <td class="px-6 py-4 text-gray-700">RM {{ number_format($transaction->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ $transaction->category }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $transaction->transaction_date->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="hf-pagination border-t border-gray-200 px-6 py-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
