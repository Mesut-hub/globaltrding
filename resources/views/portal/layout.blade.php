<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Customer Portal — Global Trading</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
<div class="flex min-h-screen">
  <aside class="w-60 flex-shrink-0 bg-[#0B1220] text-white flex flex-col">
    <div class="px-5 py-5 border-b border-white/10">
      <div class="font-extrabold text-sm">GLOBAL <span class="text-[#C99A3D]">TRADING</span></div>
      <div class="text-[11px] text-slate-400 mt-0.5">Customer portal</div>
    </div>
    <nav class="p-3 flex flex-col gap-1 flex-1">
      <a href="{{ route('portal.home', app()->getLocale()) }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.home') ? 'bg-[#C99A3D]/10 border-l-2 border-[#C99A3D] text-white' : 'text-slate-300 hover:bg-white/5' }}">Global Trading home</a>
      <a href="{{ route('portal.orders.index', app()->getLocale()) }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.orders.*') ? 'bg-[#C99A3D]/10 border-l-2 border-[#C99A3D] text-white' : 'text-slate-300 hover:bg-white/5' }}">Orders</a>
      <a href="{{ route('portal.status', app()->getLocale()) }}" class="px-3 py-2 rounded-lg text-sm {{ request()->routeIs('portal.status') ? 'bg-[#C99A3D]/10 border-l-2 border-[#C99A3D] text-white' : 'text-slate-300 hover:bg-white/5' }}">Order status</a>
    </nav>
  </aside>

  <div class="flex-1 min-w-0">
    <div class="h-15 bg-white border-b border-slate-200 flex items-center justify-end px-6 relative" x-data="{open:false}">
      <button @click="open = !open" class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-medium hover:bg-slate-50">
        {{ Auth::guard('customer')->user()->company_name }}
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m6 9 6 6 6-6"/></svg>
      </button>
      <div x-show="open" @click.outside="open=false" x-cloak class="absolute top-14 right-6 w-52 bg-white border border-slate-200 rounded-lg shadow-lg p-1">
        <a href="#" class="block px-3 py-2 rounded-md text-sm hover:bg-slate-50">Company profile</a>
        <hr class="my-1 border-slate-200">
        <form method="POST" action="{{ route('portal.logout', app()->getLocale()) }}">@csrf
          <button class="w-full text-left px-3 py-2 rounded-md text-sm text-red-600 hover:bg-red-50">Log out</button>
        </form>
      </div>
    </div>
    <main class="p-7">
      {{ $slot }}
    </main>
  </div>
</div>
</body>
</html>