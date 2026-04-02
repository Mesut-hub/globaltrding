document.addEventListener('DOMContentLoaded', () => {
  // Header scroll state
  const header = document.getElementById('siteHeader');
  if (header) {
    const onScroll = () => {
      if (window.scrollY > 80) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Language menu
  const btn = document.getElementById('langBtn');
  const menu = document.getElementById('langMenu');

  btn?.addEventListener('click', () => {
    menu?.classList.toggle('open');
  });

  document.addEventListener('click', (e) => {
    if (!menu || !btn) return;
    if (menu.contains(e.target) || btn.contains(e.target)) return;
    menu.classList.remove('open');
  });

  // Search overlay
  const openBtn = document.getElementById('searchOpen');
  const overlay = document.getElementById('searchOverlay');
  const closeBtn = document.getElementById('searchClose');

  const open = () => {
    overlay?.classList.remove('hidden');
    setTimeout(() => document.getElementById('siteSearchInput')?.focus(), 50);
  };
  const close = () => overlay && overlay.classList.add('hidden');

  const input = document.getElementById('siteSearchInput');
  const resultsEl = document.getElementById('searchResults');
  const hintEl = document.getElementById('searchHint');
  let timer = null;

  function esc(s) {
    return (s ?? '').replace(/[&<>"']/g, (m) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])
    );
  }

  async function doSearch(q) {
    const locale = document.documentElement.lang || 'en';
    const res = await fetch(`/${locale}/search?q=${encodeURIComponent(q)}`);
    const json = await res.json();
    const items = json.results || [];

    if (!items.length) {
      resultsEl.innerHTML = `<div class="search-hint">No results found.</div>`;
      return;
    }

    resultsEl.innerHTML = items
      .map((item) => {
        const img = item.image
          ? `<img class="search-result__img" src="${esc(item.image)}" alt="">`
          : `<div class="search-result__img">${esc(item.type)}</div>`;

        return `
          <a class="search-result" href="${esc(item.url)}">
            ${img}
            <div>
              <div class="search-result__title">${esc(item.title)}</div>
              <div class="search-result__meta">${esc(item.type)}</div>
            </div>
          </a>
        `;
      })
      .join('');
  }

  input?.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(timer);

    if (q.length < 3) {
      hintEl.textContent = 'Type at least 3 characters…';
      resultsEl.innerHTML = '';
      return;
    }

    hintEl.textContent = 'Searching…';
    timer = setTimeout(() => doSearch(q), 250);
  });

  openBtn?.addEventListener('click', open);
  closeBtn?.addEventListener('click', close);

  overlay?.addEventListener('click', (e) => {
    if (e.target === overlay) close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') close();
  });
});