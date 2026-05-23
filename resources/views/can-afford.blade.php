@extends('layouts.app')

@section('content')
<div class="hf-card mx-auto max-w-xl rounded-2xl p-8">
    <h1 class="hf-title mb-4 text-3xl font-black tracking-tight">Can I Afford This?</h1>

    @isset($ptptnMetrics)
        @if($ptptnMetrics['enabled'])
            <div class="hf-note mb-5 rounded-lg p-4 text-sm">
                <p class="font-semibold">PTPTN Mode is active</p>
                <p class="mt-1">This checker now protects your RM {{ number_format($ptptnMetrics['recommended_reserve'], 2) }} reserve and RM {{ number_format($ptptnMetrics['daily_safe_spend'], 2) }} daily safe spend before approving wants.</p>
            </div>
        @endif
    @endisset

    <form method="POST" action="{{ route('can-afford.check') }}">
        @csrf
        <div class="mb-4">
            <label for="item_name" class="hf-label-text">Item name</label>
            <input id="item_name" type="text" name="item_name" class="hf-input mt-1" required>
        </div>
        <div class="mb-4">
            <label for="item_price" class="hf-label-text">Price (RM)</label>
            <input id="item_price" type="number" step="0.01" name="item_price" class="hf-input mt-1" required>
        </div>
        <button type="submit" class="hf-btn w-full rounded-lg px-4 py-2.5 font-semibold">Check</button>
    </form>

    @isset($answer)
        <div class="hf-note mt-6 rounded-lg p-4">
            <p class="font-bold">{{ $answer }}</p>
            <p class="mt-1">{{ $advice }}</p>
            <p class="hf-ai-warning mt-3">
                <span class="font-semibold">AI note:</span> {{ $funMessage }}
            </p>
        </div>
    @endisset
</div>
@endsection
