<x-portal-layout>
  <h1 class="text-xl font-bold mb-1">{{ __('portal.home.welcome', ['company' => $customer->company_name]) }}</h1>
  <p class="text-sm text-slate-500 mb-6">{{ __('portal.home.subtitle') }}</p>
  <div class="grid grid-cols-2 gap-4">
    <a href="{{ route('portal.orders.index', app()->getLocale()) }}" class="bg-white border border-slate-200 rounded-lg p-6 hover:bg-slate-50">
      <div class="font-semibold text-sm mb-1">{{ __('portal.home.orders_title') }}</div>
      <div class="text-xs text-slate-500">{{ __('portal.home.orders_desc') }}</div>
    </a>
    <a href="{{ route('portal.status', app()->getLocale()) }}" class="bg-white border border-slate-200 rounded-lg p-6 hover:bg-slate-50">
      <div class="font-semibold text-sm mb-1">{{ __('portal.home.status_title') }}</div>
      <div class="text-xs text-slate-500">{{ __('portal.home.status_desc') }}</div>
    </a>
  </div>
</x-portal-layout>