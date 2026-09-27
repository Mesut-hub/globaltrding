<x-portal-layout>
  <h1 class="text-xl font-bold mb-1">Welcome, {{ $customer->company_name }}</h1>
  <p class="text-sm text-slate-500 mb-6">Use the sidebar to view your orders or track your latest shipment.</p>
  <div class="grid grid-cols-2 gap-4">
    <a href="{{ route('portal.orders.index', app()->getLocale()) }}" class="bg-white border border-slate-200 rounded-lg p-6 hover:bg-slate-50">
      <div class="font-semibold text-sm mb-1">Orders</div>
      <div class="text-xs text-slate-500">View your order history</div>
    </a>
    <a href="{{ route('portal.status', app()->getLocale()) }}" class="bg-white border border-slate-200 rounded-lg p-6 hover:bg-slate-50">
      <div class="font-semibold text-sm mb-1">Order status</div>
      <div class="text-xs text-slate-500">Track your latest shipment</div>
    </a>
  </div>
</x-portal-layout>