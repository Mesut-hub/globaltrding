// A tiny helper for Filament/Livewire actions to copy text to the clipboard.
window.gtClipboard = {
  async copy(text) {
    try {
      if (navigator?.clipboard?.writeText) {
        await navigator.clipboard.writeText(String(text ?? ''));
        return true;
      }

      // Fallback for older browsers
      const el = document.createElement('textarea');
      el.value = String(text ?? '');
      el.setAttribute('readonly', '');
      el.style.position = 'fixed';
      el.style.left = '-9999px';
      document.body.appendChild(el);
      el.select();
      const ok = document.execCommand('copy');
      document.body.removeChild(el);
      return ok;
    } catch (e) {
      return false;
    }
  },
};