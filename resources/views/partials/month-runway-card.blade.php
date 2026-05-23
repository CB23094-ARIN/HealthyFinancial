<div class="hf-soft-card flex h-full flex-col justify-center rounded-lg p-5 text-center">
    <p class="hf-kicker text-xs font-semibold uppercase tracking-wide">Month runway</p>
    <div class="mt-3 flex items-baseline justify-center gap-1">
        <span class="hf-title text-5xl font-black">{{ $budgetRunway['days_left'] }}</span>
        <span class="text-sm font-semibold text-indigo-800">{{ $budgetRunway['days_left'] === 1 ? 'day' : 'days' }}</span>
    </div>
    <p class="hf-kicker mt-1 text-xs font-medium">left this month</p>
    <p class="hf-note mt-4 rounded-md px-3 py-2 text-xs leading-5">
        <span class="font-semibold">AI note:</span> {{ $budgetRunwayNote }}
    </p>
    <p class="hf-muted mt-3 text-xs">
        Safe daily spend: RM {{ number_format($budgetRunway['daily_safe_spend'], 2) }}
    </p>
</div>
