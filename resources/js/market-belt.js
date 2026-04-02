function fmt(slug, v) {
  if (v === null || v === undefined) return null;

  // FX pairs: 4 decimals
  if (slug === 'usd-try' || slug === 'eur-try' || slug === 'gbp-try') {
    return Number(v).toLocaleString(undefined, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
  }

  // Gold/Brent fallback formatting
  return Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 });
}

document.addEventListener('DOMContentLoaded', async () => {
  const belt = document.querySelector('[data-market-belt]');
  if (!belt) return;

  const url = belt.getAttribute('data-market-url');
  if (!url) return;

  try {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) return;
    const data = await res.json();

    belt.querySelectorAll('[data-instrument]').forEach((el) => {
      const slug = el.getAttribute('data-instrument');
      const row = data?.[slug];

      // If no data or null value => hide chip (professional)
      if (!row || row.value === null || row.value === undefined) {
        el.classList.add('hidden');
        return;
      }

      const priceEl = el.querySelector('[data-price]');
      if (priceEl) priceEl.textContent = fmt(slug, row.value) ?? '—';

      // Tooltip with last update date
      el.title = row.date ? `Last update: ${row.date}` : '';
    });
  } catch (e) {
    // silent fail
  }
});