<div class="flex h-full flex-col justify-center rounded-lg border border-emerald-300 bg-white p-5 text-center shadow-sm">
    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Month runway</p>
    <div class="mt-3 flex items-baseline justify-center gap-1">
        <span class="text-5xl font-black text-emerald-700">{{ $budgetRunway['days_left'] }}</span>
        <span class="text-sm font-semibold text-emerald-900">{{ $budgetRunway['days_left'] === 1 ? 'day' : 'days' }}</span>
    </div>
    <p class="mt-1 text-xs font-medium text-emerald-700">left this month</p>
    <p class="mt-4 rounded-md bg-emerald-50 px-3 py-2 text-xs leading-5 text-emerald-900">
        <span class="font-semibold">AI note:</span> {{ $budgetRunwayNote }}
    </p>
    <p class="mt-3 text-xs text-gray-500">
        Safe daily spend: RM {{ number_format($budgetRunway['daily_safe_spend'], 2) }}
    </p>
</div>
