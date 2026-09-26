<x-portal-layout>
  <h1 class="text-xl font-bold mb-1">Orders</h1>
  <p class="text-sm text-slate-500 mb-5">Your order history with Global Trading.</p>
  <div class="bg-white border border-slate-200 rounded-lg divide-y divide-slate-100">
    @forelse($orders as $order)
      <a href="{{ route('portal.orders.show', [app()->getLocale(), $order->id]) }}" class="flex items-center justify-between px-5 py-4 hover:bg-slate-50">
        <div>
          <div class="font-semibold text-sm">{{ $order->product_name }} — {{ rtrim(rtrim((string)$order->quantity,'0'),'.') }} {{ $order->quantity_unit }}</div>
          <div class="text-xs text-slate-500 mt-0.5">Order {{ $order->order_number }} · placed {{ $order->order_date?->format('d M Y') }}</div>
        </div>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $order->isDelivered() ? 'bg-[#FBF1DD] text-[#7A5A1E]' : 'bg-green-50 text-green-700' }}">
          {{ $order->isDelivered() ? 'Delivered' : 'In progress' }}
        </span>
      </a>
    @empty
      <div class="px-5 py-8 text-sm text-slate-500 text-center">No confirmed orders yet.</div>
    @endforelse
  </div>
</x-portal-layout>