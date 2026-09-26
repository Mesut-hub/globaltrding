<x-portal-layout>
  <a href="{{ route('portal.orders.index', app()->getLocale()) }}" class="text-sm text-slate-500 hover:text-slate-900">← Back to Orders</a>
  <h1 class="text-xl font-bold mt-2 mb-1">{{ $order->product_name }}</h1>
  <p class="text-xs text-slate-500 font-mono mb-5">{{ $order->order_number }}</p>

  <div class="bg-white border border-slate-200 rounded-lg p-6 grid grid-cols-3 gap-5">
    <div><div class="text-[11px] text-slate-500 mb-1">Quantity</div><div class="text-sm font-semibold">{{ rtrim(rtrim((string)$order->quantity,'0'),'.') }} {{ $order->quantity_unit }}</div></div>
    <div><div class="text-[11px] text-slate-500 mb-1">Payment term</div><div class="text-sm font-semibold">{{ $order->payment_term ?? '—' }}</div></div>
    <div><div class="text-[11px] text-slate-500 mb-1">Contracted</div><div class="text-sm font-semibold">{{ $order->is_contracted ? 'Yes' : 'No' }}</div></div>
    <div><div class="text-[11px] text-slate-500 mb-1">Delivery time</div><div class="text-sm font-semibold">{{ $order->delivery_time ?? '—' }}</div></div>
    <div><div class="text-[11px] text-slate-500 mb-1">Shipping term</div><div class="text-sm font-semibold">{{ $order->shipping_term ?? '—' }}</div></div>
    <div><div class="text-[11px] text-slate-500 mb-1">Delivery point</div><div class="text-sm font-semibold">{{ Order::deliveryPointOptions()[$order->delivery_point] ?? $order->delivery_address ?? '—' }}</div></div>
    <div><div class="text-[11px] text-slate-500 mb-1">Order date</div><div class="text-sm font-semibold">{{ $order->order_date?->format('d M Y') ?? '—' }}</div></div>
  </div>

  <p class="text-sm text-slate-500 mt-4">For live tracking, see <a href="{{ route('portal.status', [app()->getLocale(), $order->id]) }}" class="text-[#7A5A1E] font-semibold">Order status</a>.</p>
</x-portal-layout>