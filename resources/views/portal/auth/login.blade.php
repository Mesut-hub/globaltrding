<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Customer Portal — Sign in</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">
<div class="flex min-h-screen">
  <div class="flex-1 flex items-center justify-center p-10">
    <div class="w-full max-w-sm">

      <div id="loginForm">
        <p class="text-xs font-bold text-[#7A5A1E] uppercase tracking-wide mb-2">Customer portal</p>
        <h1 class="text-2xl font-bold mb-1">Sign in to your account</h1>
        <p class="text-sm text-slate-500 mb-6">Track your orders and shipments with Global Trading.</p>

        @if ($errors->any())
          <div class="mb-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('portal.login.post', app()->getLocale()) }}" class="space-y-4">
          @csrf
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Username</label>
            <input name="username" value="{{ old('username') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Password</label>
            <input type="password" name="password" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required>
          </div>
          <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
          <button class="w-full bg-[#C99A3D] hover:bg-[#B98A2E] text-[#241A05] font-bold py-2.5 rounded-lg text-sm">Log in</button>
        </form>
        <div class="text-center mt-4">
          <button type="button" id="showResetBtn" class="text-sm font-semibold text-[#7A5A1E] hover:underline">Forgot password?</button>
        </div>
      </div>

      <div id="resetForm" style="display:none;">
        <p class="text-xs font-bold text-[#7A5A1E] uppercase tracking-wide mb-2">Reset access</p>
        <h1 class="text-2xl font-bold mb-1">Reset your password</h1>
        <p class="text-sm text-slate-500 mb-6">Confirm the details on file and we'll email a new password to your registered contacts.</p>

        @if (session('reset_submitted'))
          <div class="mb-4 text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">If those details match our records, a new password has been emailed to your registered contacts.</div>
        @endif

        <form method="POST" action="{{ route('portal.reset.post', app()->getLocale()) }}" class="space-y-4">
          @csrf
          <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Customer ID</label><input name="customer_code" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Company or contact name</label><input name="name" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1.5">Phone number</label><input name="phone" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm" required></div>
          <button class="w-full bg-[#C99A3D] hover:bg-[#B98A2E] text-[#241A05] font-bold py-2.5 rounded-lg text-sm">Reset password</button>
        </form>
        <div class="text-center mt-4">
          <button type="button" id="backToLoginBtn" class="text-sm font-semibold text-[#7A5A1E] hover:underline">Back to sign in</button>
        </div>
      </div>

    </div>
  </div>
  <div class="flex-1 bg-gradient-to-br from-[#0B1220] to-[#1B2740] hidden md:flex items-center justify-center p-10 overflow-y-auto">
  @php($loginPage = \App\Models\Page::where('slug', 'portal-login')->where('is_published', true)->first())
  @if($loginPage)
    <div class="text-white w-full max-w-md">
      @foreach($loginPage->blocks ?? [] as $block)
        @include('shared.blocks.render', ['block' => $block])
      @endforeach
    </div>
  @else
    <div class="border border-dashed border-white/25 rounded-2xl p-10 max-w-sm text-center text-slate-300">
      <p class="inline-block text-[11px] font-bold uppercase tracking-wide text-[#C99A3D] bg-[#C99A3D]/15 px-2.5 py-1 rounded-full mb-3">Your content here</p>
      <h3 class="text-white text-lg font-bold mb-2">This side is yours</h3>
      <p class="text-sm">Create a Page with the slug <code class="bg-white/10 px-1.5 py-0.5 rounded">portal-login</code> in your CMS, add any blocks you like, and they'll appear here automatically.</p>
    </div>
  @endif
</div>
</div>
@if($recaptchaSiteKey)<script src="https://www.google.com/recaptcha/api.js" async defer></script>@endif
<script>
  document.getElementById('showResetBtn').addEventListener('click', function () {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('resetForm').style.display = '';
  });
  document.getElementById('backToLoginBtn').addEventListener('click', function () {
    document.getElementById('resetForm').style.display = 'none';
    document.getElementById('loginForm').style.display = '';
  });
  @if(session('reset_submitted'))
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('resetForm').style.display = '';
  @endif
</script>
</body>
</html>