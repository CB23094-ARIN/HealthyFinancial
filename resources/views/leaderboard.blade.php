@extends('layouts.app')

@section('content')
<div class="hf-card rounded-2xl p-6">
    <h1 class="hf-title mb-2 text-3xl font-black tracking-tight">Leaderboard</h1>
    <p class="hf-muted mb-5 text-sm">PTPTN Mode students can earn a small bonus when their monthly reserve stays protected.</p>
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
            @foreach($leaderboard as $index => $entry)
            <tr class="border-b">
                <td class="px-4 py-3">{{ $index + 1 }}</td>
                <td class="px-4 py-3 font-medium">
                    {{ $entry->user->name ?? 'Unknown' }}
                    @if($entry->user?->ptptn_mode)
                    @endif
                </td>
                <td class="px-4 py-3">{{ $entry->university_name }}</td>
                <td class="px-4 py-3 font-bold text-emerald-600">{{ $entry->points }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
