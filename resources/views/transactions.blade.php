@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-medium uppercase tracking-wide text-emerald-700">Transactions</p>
            <h1 class="mt-1 text-3xl font-bold text-gray-950">All transactions</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg bg-gray-950 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
            Add transaction
        </a>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('transactions') }}" class="grid grid-cols-1 items-end gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)_8rem_8rem]">
            <div>
                <label for="transaction_search" class="block text-sm font-medium text-gray-700">Search description or category</label>
                <input id="transaction_search" type="search" name="search" value="{{ $search }}" placeholder="Search description or category" class="mt-1 h-11 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
            </div>

            <div>
                <label for="transaction_filter_category" class="block text-sm font-medium text-gray-700">Filter by category</label>
                <select id="transaction_filter_category" name="category" class="mt-1 h-11 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
                    <option value="">All categories</option>
                    @foreach($transactionCategories as $category)
                        <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="h-11 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700">Search</button>

            @if($search || $selectedCategory)
                <a href="{{ route('transactions') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Clear
                </a>
            @else
                <span class="hidden h-11 lg:block"></span>
            @endif
        </form>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
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

        <div class="border-t border-gray-200 px-6 py-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
