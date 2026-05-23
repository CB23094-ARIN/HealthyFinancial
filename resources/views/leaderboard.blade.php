@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Leaderboard</h1>
    <p class="mb-4 text-sm text-gray-500">PTPTN Mode students can earn a small bonus when their monthly reserve stays protected.</p>
    <table class="w-full text-left">
        <thead>
            <tr class="border-b">
                <th>Rank</th>
                <th>Name</th>
                <th>Campus</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leaderboard as $index => $entry)
            <tr class="border-b">
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $entry->user->name ?? 'Unknown' }}
                    @if($entry->user?->ptptn_mode)
                        <span class="ml-2 rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">PTPTN</span>
                    @endif
                </td>
                <td>{{ $entry->campus }}</td>
                <td class="font-bold text-emerald-600">{{ $entry->points }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
