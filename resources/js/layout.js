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
  // Language dropdown (delegated, Livewire/DOM-safe)
  const closeAllLang = () => {
    document.querySelectorAll('[data-lang-dropdown][data-open="1"]').forEach(dd => {
      dd.removeAttribute('data-open');
    });
  };

  document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-lang-toggle]');
    if (!toggle) {
      // outside click closes
      closeAllLang();
      return;
    }

    e.preventDefault();
    e.stopPropagation();

    const dropdown = toggle.closest('[data-lang-dropdown]');
    if (!dropdown) return;

    const isOpen = dropdown.getAttribute('data-open') === '1';
    closeAllLang();
    if (!isOpen) dropdown.setAttribute('data-open', '1');
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllLang();
  });

  // Search overlay
  const openBtn = document.getElementById('searchOpen');
  const overlay = document.getElementById('searchOverlay');
  const closeBtn = document.getElementById('searchClose');

  const input = document.getElementById('siteSearchInput');
  const resultsEl = document.getElementById('searchResults');
  const hintEl = document.getElementById('searchHint');

  let timer = null;
  let activeIndex = -1;

  function lockBody(lock) {
    document.documentElement.classList.toggle('overflow-hidden', lock);
    document.body.classList.toggle('overflow-hidden', lock);
  }

  function setOpenState(isOpen) {
    if (!overlay) return;

    overlay.classList.toggle('hidden', !isOpen);
    overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

    lockBody(isOpen);

    if (!isOpen) {
      activeIndex = -1;
      // optional: keep previous search text; if you prefer clearing, uncomment:
      // if (input) input.value = '';
      if (resultsEl) resultsEl.innerHTML = '';
      if (hintEl) hintEl.textContent = 'Type at least 3 characters…';
    } else {
      setTimeout(() => input?.focus(), 50);
    }
  }

  const open = () => setOpenState(true);
  const close = () => setOpenState(false);

  function esc(s) {
    return (s ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  function getResultLinks() {
    return Array.from(resultsEl?.querySelectorAll('a.search-result') ?? []);
  }

  function setActiveResult(index) {
    const links = getResultLinks();
    activeIndex = index;

    links.forEach((a, i) => {
      a.classList.toggle('is-active', i === activeIndex);
      if (i === activeIndex) {
        a.scrollIntoView({ block: 'nearest' });
      }
    });
  }

  async function doSearch(q) {
    const locale = document.documentElement.lang || 'en';

    const res = await fetch(`/${locale}/search?q=${encodeURIComponent(q)}`, {
      headers: { Accept: 'application/json' },
    });

    const json = await res.json();
    const items = json.results || [];

    if (!items.length) {
      resultsEl.innerHTML = `<div class="search-hint">No results found.</div>`;
      activeIndex = -1;
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

    // reset selection to first item
    setActiveResult(0);
  }

  input?.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(timer);

    if (q.length < 3) {
      hintEl.textContent = 'Type at least 3 characters…';
      resultsEl.innerHTML = '';
      activeIndex = -1;
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

  // Global key handling
  document.addEventListener('keydown', (e) => {
    const isOpen = overlay && !overlay.classList.contains('hidden');

    // "/" opens search if not typing in an input/textarea
    if (!isOpen && e.key === '/') {
      const tag = document.activeElement?.tagName?.toLowerCase();
      const typing =
        tag === 'input' ||
        tag === 'textarea' ||
        document.activeElement?.getAttribute?.('contenteditable') === 'true';

      if (!typing) {
        e.preventDefault();
        open();
      }
      return;
    }

    if (!isOpen) return;

    if (e.key === 'Escape') {
      e.preventDefault();
      close();
      return;
    }

    // Keyboard navigation inside open overlay
    const links = getResultLinks();
    if (!links.length) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActiveResult(Math.min(activeIndex + 1, links.length - 1));
      return;
    }

    if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActiveResult(Math.max(activeIndex - 1, 0));
      return;
    }

    if (e.key === 'Enter') {
      // If we have an active item, follow it
      if (activeIndex >= 0 && links[activeIndex]) {
        e.preventDefault();
        window.location.href = links[activeIndex].href;
      }
    }
  });
});