<x-portal-bare-layout title="Set your password">
  <div class="max-w-sm mx-auto pt-20">
    <h1 class="text-xl font-bold mb-1">Set your password</h1>
    <p class="text-sm text-slate-500 mb-6">For your security, please choose your own password before continuing.</p>
    <form method="POST" action="{{ route('portal.password.change.post', app()->getLocale()) }}" class="space-y-4">
      @csrf
      <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">New password</label><input type="password" name="password" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
      <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Confirm password</label><input type="password" name="password_confirmation" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
      <button class="w-full bg-[#C99A3D] text-[#241A05] font-bold py-2.5 rounded-lg text-sm">Save password</button>
    </form>
  </div>
</x-portal-bare-layout>