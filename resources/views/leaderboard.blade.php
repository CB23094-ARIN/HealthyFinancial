@extends('layouts.app')

@section('content')
@php
    $topEntry = $leaderboard->first();
@endphp

<div class="space-y-6">
    <div class="hf-card rounded-2xl p-6">
        <h1 class="hf-title mb-2 text-3xl font-black tracking-tight">Leaderboard</h1>
        <p class="hf-muted mb-5 text-sm">
            Earn points from transactions, active tracking days, saving streaks, and PTPTN Mode discipline.
        </p>

        @if($topEntry)
            <div class="mb-6 rounded-2xl border border-indigo-300 bg-indigo-50 p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('images/reward.png') }}" alt="" class="h-16 w-16 shrink-0">
                        <div>
                            <p class="text-xl font-black uppercase tracking-wide text-indigo-700">
                                Congratulations
                                <span class="rounded-full bg-white px-3 py-1 font-black text-indigo-700">
                                    {{ $topEntry->user->name ?? 'Unknown' }}
                                </span>
                            </p>
                        <h2 class="mt-1 text-2xl font-black text-gray-950">Top 1 Financial Champion</h2>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white px-5 py-4 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Rank 1</p>
                        <p class="mt-1 text-3xl font-black text-indigo-700">{{ $topEntry->points }}</p>
                        <p class="text-xs text-gray-500">points</p>
                    </div>
                </div>
            </div>
        @endif

        <table class="w-full text-left">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr class="border-b">
                    <th class="px-4 py-3">Rank</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">University name</th>
                    <th class="px-4 py-3">Points</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaderboard as $index => $entry)
                    <tr class="border-b">
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span>{{ $index + 1 }}</span>
                                @if($index === 0)

                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium">
                            {{ $entry->user->name ?? 'Unknown' }}
                        </td>
                        <td class="px-4 py-3">{{ $entry->university_name }}</td>
                        <td class="px-4 py-3 font-bold text-emerald-600">{{ $entry->points }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                            No leaderboard entries yet. Add a transaction to start earning points.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
