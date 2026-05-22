@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Leaderboard</h1>
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
                <td>{{ $entry->user->name ?? 'Unknown' }}</td>
                <td>{{ $entry->campus }}</td>
                <td class="font-bold text-emerald-600">{{ $entry->points }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection