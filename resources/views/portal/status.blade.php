@php($isDelivered = $order?->isDelivered() ?? false)
<x-portal-layout>
  <h1 class="text-xl font-bold mb-1">{{ __('portal.status.title') }}</h1>
  <p class="text-sm text-slate-500 mb-5">{{ __('portal.status.subtitle') }}</p>
  @if($allOrders->count() > 1)
    <div class="mb-5 max-w-xs">
      <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('portal.status.order_select') }}</label>
      <select class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white"
              onchange="window.location = '{{ route('portal.status', app()->getLocale()) }}/' + this.value;">
        @foreach($allOrders as $o)
          <option value="{{ $o->id }}" {{ $order?->id === $o->id ? 'selected' : '' }}>{{ $o->order_number }} — {{ $o->product_name }}</option>
        @endforeach
      </select>
    </div>
  @endif

  @if(!$order)
    <div class="bg-white border border-slate-200 rounded-lg p-8 text-center text-sm text-slate-500">{{ __('portal.status.no_orders') }}</div>
  @else
    <div class="grid grid-cols-[1.15fr_.85fr] gap-4 mb-6">
      <div class="bg-white border border-slate-200 rounded-lg p-6">
        <div class="text-xs text-slate-500 font-mono mb-1">{{ $order->order_number }}</div>
        <h2 class="text-lg font-bold mb-4">{{ $order->product_name }}</h2>
        <div class="grid grid-cols-2 gap-4">
          <div><div class="text-[11px] text-slate-500">{{ __('portal.status.quantity') }}</div><div class="text-sm font-semibold">{{ rtrim(rtrim((string)$order->quantity,'0'),'.') }} {{ $order->quantity_unit }}</div></div>
          <div><div class="text-[11px] text-slate-500">{{ __('portal.status.delivery_point') }}</div><div class="text-sm font-semibold">{{ $order->delivery_point && $order->delivery_point !== 'other' ? __('portal.delivery_points.' . $order->delivery_point) : ($order->delivery_address ?? '—') }}</div></div>
        </div>
      </div>

      @if($isDelivered)
        <div class="rounded-lg p-6 text-white flex flex-col justify-center" style="background:linear-gradient(160deg,#0F3D24,#146132);">
          <div class="text-[11px] font-bold uppercase tracking-wide text-[#8FE3AE]">{{ __('portal.status.delivered_flag') }}</div>
          <h3 class="text-lg font-bold mt-2">{{ __('portal.status.delivered_title') }}</h3>
          <div class="text-xs text-[#B9E8C9] mt-1.5">{{ __('portal.status.delivered_confirmed', ['date' => $order->statusUpdates()->where('stage_key','delivered')->value('stage_date')?->format('d M Y'), 'point' => $order->delivery_point && $order->delivery_point !== 'other' ? __('portal.delivery_points.' . $order->delivery_point) : ($order->delivery_address ?? '')]) }}</div>
        </div>
      @else
        <div class="rounded-lg p-6 text-white flex flex-col justify-center" style="background:linear-gradient(160deg,#0B1220,#1B2740);">
          <div class="text-[11px] text-slate-400 uppercase tracking-wide">{{ __('portal.status.current_stage') }}</div>
          <div class="text-lg font-bold mt-2">{{ $updates->last() ? (\App\Enums\OrderStatusStage::tryFrom($updates->last()->stage_key)?->label() ?? $updates->last()->stage_label) : __('portal.status.awaiting_first_update') }}</div>
          <div class="text-xs text-slate-400 mt-1.5">{{ $updates->last()?->stage_date?->format('d M Y') }}</div>
        </div>
      @endif
    </div>

    <div class="bg-white border border-slate-200 rounded-lg p-6">
      <h2 class="text-sm font-bold mb-5">{{ __('portal.status.cargo_tracking') }}</h2>
      <div class="pl-1">
        @foreach($updates as $u)
          <div class="relative pl-8 pb-6">
            @if(!$loop->last)<div class="absolute left-[9px] top-6 bottom-0 w-0.5 bg-[#C99A3D]"></div>@endif
            <div class="absolute left-0 top-0.5 w-5 h-5 rounded-full bg-[#C99A3D] flex items-center justify-center">
              <svg class="w-2.5 h-2.5 text-[#241A05]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <div class="text-sm font-semibold">{{ \App\Enums\OrderStatusStage::tryFrom($u->stage_key)?->label() ?? $u->stage_label }}</div>
            <div class="text-xs text-slate-500 mt-0.5">{{ $u->stage_date->format('d M Y') }}@if($u->notes) — {{ $u->notes }}@endif</div>
          </div>
        @endforeach
      </div>
    </div>
  @endif
</x-portal-layout>