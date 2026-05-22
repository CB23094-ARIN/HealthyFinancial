@extends('layouts.app')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Can I Afford This?</h1>
    <form method="POST" action="{{ route('can-afford.check') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-gray-700">Item name</label>
            <input type="text" name="item_name" class="border rounded-lg p-2 w-full" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Price (RM)</label>
            <input type="number" step="0.01" name="item_price" class="border rounded-lg p-2 w-full" required>
        </div>
        <button type="submit" class="bg-emerald-500 text-white rounded-lg px-4 py-2 w-full">Check</button>
    </form>

    @isset($answer)
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="font-bold">{{ $answer }}</p>
            <p>{{ $advice }}</p>
            <p class="text-emerald-600 mt-2 italic">{{ $funMessage }}</p>
        </div>
    @endisset
</div>
@endsection
