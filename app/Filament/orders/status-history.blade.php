<div class="space-y-3 mb-4">
    @forelse($order->statusUpdates as $u)
        <div class="flex items-start gap-3 text-sm border-b border-gray-100 pb-2">
            <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5 flex-shrink-0"></div>
            <div>
                <div class="font-semibold">{{ $u->stage_label }}</div>
                <div class="text-gray-500 text-xs">{{ $u->stage_date->format('d M Y') }}@if($u->notes) — {{ $u->notes }}@endif</div>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">No status updates recorded yet.</p>
    @endforelse
</div>