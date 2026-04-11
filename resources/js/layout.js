document.addEventListener('DOMContentLoaded', () => {
    
    // fixed header: expose height as CSS variable
  const headerEl = document.getElementById('siteHeader');
  const setHeaderVar = () => {
    if (!headerEl) return;
    document.documentElement.style.setProperty('--header-h', `${headerEl.offsetHeight}px`);
  };
  setHeaderVar();
  window.addEventListener('resize', setHeaderVar, { passive: true });
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

    // Generic horizontal carousel (cards)
  document.querySelectorAll('[data-carousel]').forEach((root) => {
    const track = root.querySelector('[data-carousel-track]');
    const prev = root.querySelector('[data-carousel-prev]');
    const next = root.querySelector('[data-carousel-next]');
    if (!track) return;

    const autoplay = root.getAttribute('data-carousel-autoplay') === '1';
    const interval = Math.max(1500, Number(root.getAttribute('data-carousel-interval') || '4500'));
    const pauseHover = root.getAttribute('data-carousel-pause-hover') === '1';

    let paused = false;
    let timer = null;

    const step = () => Math.min(track.clientWidth * 0.9, 700);

    prev?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next?.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));

    if (pauseHover) {
      root.addEventListener('mouseenter', () => { paused = true; });
      root.addEventListener('mouseleave', () => { paused = false; });
    }

    if (autoplay) {
      timer = setInterval(() => {
        if (paused) return;
        // loop effect: if near end, go back to start
        const eps = 4;
        const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - eps;
        if (atEnd) track.scrollTo({ left: 0, behavior: 'smooth' });
        else track.scrollBy({ left: step(), behavior: 'smooth' });
      }, interval);
    }
  });

    // Count-up animation
  const countEls = Array.from(document.querySelectorAll('[data-countup]'));
  if (countEls.length) {
    const seen = new WeakSet();

    const format = (n) => {
      // integer formatting
      return Math.round(n).toLocaleString();
    };

    const animate = (el) => {
      if (seen.has(el)) return;
      seen.add(el);

      const target = Number(el.getAttribute('data-countup') || '0');
      const suffix = el.getAttribute('data-countup-suffix') || '';
      const start = 0;
      const dur = 900;

      const t0 = performance.now();

      const tick = (t) => {
        const p = Math.min(1, (t - t0) / dur);
        // ease-out
        const eased = 1 - Math.pow(1 - p, 3);
        const val = start + (target - start) * eased;
        el.textContent = `${format(val)}${suffix}`;
        if (p < 1) requestAnimationFrame(tick);
      };

      requestAnimationFrame(tick);
    };

    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) animate(e.target);
      });
    }, { threshold: 0.25 });

    countEls.forEach((el) => io.observe(el));
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

  // Cookie consent (categories) + GA4 (analytics consent gated)
  const GA_ID = 'G-HLE66GHDML';

  // Stored as JSON:
  // { analytics: true|false|null, social: true|false|null }
  // null means "not chosen yet"
  const KEY = 'cookie_consent_v2';

  function readConsent() {
    try {
      const raw = localStorage.getItem(KEY);
      if (!raw) return { analytics: null, social: null };
      const v = JSON.parse(raw);
      return {
        analytics: typeof v.analytics === 'boolean' ? v.analytics : null,
        social: typeof v.social === 'boolean' ? v.social : null,
      };
    } catch {
      return { analytics: null, social: null };
    }
  }

  function writeConsent(next) {
    const cur = readConsent();
    const merged = { ...cur, ...next };
    localStorage.setItem(KEY, JSON.stringify(merged));
    window.dispatchEvent(new CustomEvent('cookie-consent:changed', { detail: merged }));
    return merged;
  }

  function loadGA() {
    if (!GA_ID) return;
    if (document.querySelector('script[data-ga="1"]')) return;

    const s1 = document.createElement('script');
    s1.async = true;
    s1.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(GA_ID)}`;
    s1.dataset.ga = '1';
    document.head.appendChild(s1);

    window.dataLayer = window.dataLayer || [];
    function gtag(){ window.dataLayer.push(arguments); }
    window.gtag = gtag;

    gtag('js', new Date());
    gtag('config', GA_ID);
  }

  const banner = document.getElementById('cookieBanner');
  const acceptBtn = document.getElementById('cookieAccept');
  const rejectBtn = document.getElementById('cookieReject');

  // Optional: create extra button in HTML for social consent (recommended)
  const acceptSocialBtn = document.getElementById('cookieAcceptSocial'); // add in Blade

  const consent = readConsent();

  // Apply existing consent
  if (consent.analytics === true) loadGA();

  // Show banner if analytics not chosen OR social not chosen
  if (consent.analytics === null || consent.social === null) {
    banner?.classList.remove('hidden');
  }

  // Accept ALL (analytics + social)
  acceptBtn?.addEventListener('click', () => {
    const next = writeConsent({ analytics: true, social: true });
    banner?.classList.add('hidden');
    if (next.analytics) loadGA();
  });

  // Reject ALL (analytics + social)
  rejectBtn?.addEventListener('click', () => {
    writeConsent({ analytics: false, social: false });
    banner?.classList.add('hidden');
  });

  // Accept SOCIAL only (no GA). This matches your requirement.
  acceptSocialBtn?.addEventListener('click', () => {
    const next = writeConsent({ social: true });
    // Keep banner visible if analytics still null (user hasn't decided analytics)
    if (next.analytics !== null) banner?.classList.add('hidden');
  });

  // Expose a tiny helper for other scripts (home.js)
  window.__cookieConsent = {
    read: readConsent,
    write: writeConsent,
  };

    // Hero slider (for blocks)
    // Hero slider: changes slide + updates kicker/title/lead/cta per slide
  document.querySelectorAll('[data-hero]').forEach((hero) => {
    const slider = hero.querySelector('[data-hero-slider]');
    if (!slider) return;

    const slides = Array.from(slider.querySelectorAll('.gt-hero__slide'));
    if (slides.length <= 1) return;

    const prev = slider.querySelector('[data-hero-prev]');
    const next = slider.querySelector('[data-hero-next]');

    const content = hero.querySelector('[data-hero-content]');
    const kickerEl = hero.querySelector('[data-hero-kicker]');
    const titleEl = hero.querySelector('[data-hero-title]');
    const leadEl = hero.querySelector('[data-hero-lead]');
    const ctaEl = hero.querySelector('[data-hero-cta]');
    const ctaWrap = hero.querySelector('[data-hero-cta-wrap]');

    let slideData = [];
    try {
      slideData = JSON.parse(content?.getAttribute('data-hero-slides') || '[]');
    } catch (e) {
      slideData = [];
    }

    const autoplay = hero.getAttribute('data-hero-autoplay') === '1';
    const interval = Math.max(1500, Number(hero.getAttribute('data-hero-interval') || '4500'));
    const pauseOnHover = hero.getAttribute('data-hero-pause-hover') === '1';

    let idx = slides.findIndex(s => s.classList.contains('is-active'));
    if (idx < 0) idx = 0;

    let timer = null;
    let paused = false;

    const pickSlideText = (i) => {
      if (!Array.isArray(slideData) || slideData.length === 0) return {};
      // If fewer slide texts than images, reuse first slide text
      return slideData[i] || slideData[0] || {};
    };

    const applyText = (i) => {
      const s = pickSlideText(i);

      const kicker = (s.kicker || '').trim();
      const title = (s.title || '').trim();
      const lead = (s.lead || '').trim();
      const ctaLabel = (s.cta_label || '').trim();
      const ctaUrl = (s.cta_url || '').trim();

      if (kickerEl) {
        kickerEl.textContent = kicker;
        kickerEl.classList.toggle('hidden', !kicker);
      }
      if (titleEl) titleEl.textContent = title || '';
      if (leadEl) {
        leadEl.textContent = lead;
        leadEl.classList.toggle('hidden', !lead);
      }
      if (ctaEl) {
        if (ctaLabel && ctaUrl) {
          ctaEl.textContent = ctaLabel;
          ctaEl.href = ctaUrl;
          ctaEl.classList.remove('hidden');
          ctaWrap?.classList.remove('hidden');
        } else {
          ctaEl.classList.add('hidden');
        }
      }
    };

    const set = (i) => {
      idx = (i + slides.length) % slides.length;
      slides.forEach((s, k) => s.classList.toggle('is-active', k === idx));
      applyText(idx);
    };

    const start = () => {
      if (!autoplay) return;
      if (timer) return;
      timer = setInterval(() => {
        if (!paused) set(idx + 1);
      }, interval);
    };

    const stop = () => {
      if (!timer) return;
      clearInterval(timer);
      timer = null;
    };

    prev?.addEventListener('click', () => set(idx - 1));
    next?.addEventListener('click', () => set(idx + 1));

    if (pauseOnHover) {
      hero.addEventListener('mouseenter', () => { paused = true; });
      hero.addEventListener('mouseleave', () => { paused = false; });
    }

    // initial text
    applyText(idx);
    start();
  });
    // ===== NAV OVERLAY (single instance) =====
  (function () {
    const overlay = document.getElementById('navOverlay');
    const btnClose = document.getElementById('navOverlayClose');
    const elTitle = document.getElementById('navOverlayTitle');
    const elList = document.getElementById('navOverlayList');
    const elDesc = document.getElementById('navOverlayDesc');
    const elPreview = document.getElementById('navOverlayPreview');

    const searchWrap = document.getElementById('navOverlaySearchWrap');
    const searchInput = document.getElementById('navOverlaySearchInput');
    const searchBtn = document.getElementById('navOverlaySearchBtn');

    if (!overlay || !elTitle || !elList || !elDesc || !elPreview) return;

    const locale = document.documentElement.lang || 'en';

    // NOTE: update these URLs/images whenever you create pages
    const NAV_DATA = {
      "who-we-are": {
        title: "Who we are",
        showSearch: false,
        defaultIndex: 0,
        items: [
          { title: "Organization", url: `/${locale}/pages/who-we-are`, desc: "Learn about our organization.", previewImage: "/images/overlay/who-we-are.jpg" },
          { title: "Strategy", url: `/${locale}/pages/strategy`, desc: "Our strategy and long-term direction.", previewImage: "/images/overlay/strategy.jpg" },
          { title: "Sustainability", url: `/${locale}/pages/sustainability`, desc: "Our sustainability approach.", previewImage: "/images/overlay/sustainability.jpg" },
          { title: "Innovation", url: `/${locale}/pages/innovation`, desc: "Innovation in products and processes.", previewImage: "/images/overlay/innovation.jpg" },
          { title: "Digitalization", url: `/${locale}/pages/digitalization`, desc: "How we use digital tools.", previewImage: "/images/overlay/digitalization.jpg" },
        ],
      },

      "products": {
        title: "Products",
        showSearch: true,
        searchUrl: `/${locale}/products`,
        defaultIndex: 0,
        items: [
          { title: "Product Finder", url: `/${locale}/products`, desc: "Search products by name, brand, or industry.", previewImage: "/images/overlay/products.jpg", isFinder: true },
          { title: "Adhesives & Sealants", url: `/${locale}/products?category=adhesives-sealants`, desc: "Adhesives and sealants.", previewImage: "/images/overlay/products.jpg" },
          { title: "Agriculture", url: `/${locale}/products?category=agriculture`, desc: "Agriculture products.", previewImage: "/images/overlay/products.jpg" },
          { title: "Chemicals", url: `/${locale}/products?category=chemicals`, desc: "Chemicals.", previewImage: "/images/overlay/products.jpg" },
        ],
      },

      "investors": {
        title: "Investors",
        showSearch: false,
        defaultIndex: 0,
        items: [
          { title: "At a glance", url: `/${locale}/pages/investors`, desc: "Key information for stakeholders.", previewImage: "/images/overlay/investors.jpg" },
          { title: "Calendar and Publications", url: `/${locale}/pages/investors-publications`, desc: "Publications and calendar.", previewImage: "/images/overlay/investors.jpg" },
          { title: "Share and ADRs", url: `/${locale}/pages/investors-share`, desc: "Share information.", previewImage: "/images/overlay/investors.jpg" },
        ],
      },

      "careers": {
        title: "Careers",
        showSearch: false,
        defaultIndex: 0,
        items: [
          { title: "Job search", url: `/${locale}/pages/careers`, desc: "Explore job opportunities.", previewImage: "/images/overlay/careers.jpg" },
          { title: "Professionals", url: `/${locale}/pages/careers-professionals`, desc: "For experienced professionals.", previewImage: "/images/overlay/careers.jpg" },
          { title: "Graduates", url: `/${locale}/pages/careers-graduates`, desc: "For graduates.", previewImage: "/images/overlay/careers.jpg" },
          { title: "Students", url: `/${locale}/pages/careers-students`, desc: "For students.", previewImage: "/images/overlay/careers.jpg" },
        ],
      },
    };

    let state = {
      key: null,
      idx: 0,
    };

    function lockScroll(lock) {
      document.documentElement.classList.toggle('overflow-hidden', lock);
      document.body.classList.toggle('overflow-hidden', lock);
    }

    function renderRight() {
      const data = NAV_DATA[state.key];
      if (!data) return;
      const item = data.items?.[state.idx] || data.items?.[0];
      if (!item) return;

      elDesc.textContent = item.desc || '';
      elPreview.innerHTML = item.previewImage ? `<img src="${item.previewImage}" alt="">` : '';
    }

    function renderLeft() {
      const data = NAV_DATA[state.key];
      if (!data) return;

      elTitle.textContent = data.title || 'Menu';

      // Search only for products; placed under title, like BASF
      const showSearch = !!data.showSearch;
      searchWrap?.classList.toggle('hidden', !showSearch);

      elList.innerHTML = (data.items || []).map((it, i) => {
        const active = i === state.idx ? 'is-active' : '';
        const safeTitle = (it.title || '').replace(/</g, '&lt;');

        const finder = it.isFinder ? `
          <div class="nav-overlay__finder" data-nav-finder>
            <input type="text"
                  class="nav-overlay__finderInput"
                  placeholder="What are you looking for?"
                  data-nav-find-input>
            <button type="button"
                    class="nav-overlay__finderBtn"
                    data-nav-find-btn
                    aria-label="Search">⌕</button>
          </div>
        ` : '';

        return `
          <div class="nav-overlay__row ${it.isFinder ? 'is-finder' : ''}">
            <div class="nav-overlay__item ${active}" data-idx="${i}">
              <div class="nav-overlay__itemTitle">${safeTitle}</div>
              <div class="nav-overlay__chev">›</div>
            </div>

            ${it.isFinder ? `
              <div class="nav-overlay__finder" data-nav-finder>
                <input type="text"
                      class="nav-overlay__finderInput"
                      placeholder="What are you looking for?"
                      data-nav-find-input>
                <button type="button"
                        class="nav-overlay__finderBtn"
                        data-nav-find-btn
                        aria-label="Search">⌕</button>
              </div>
              <div class="nav-overlay__divider"></div>
            ` : `
              <div class="nav-overlay__divider"></div>
            `}
          </div>
        `;
      }).join('');

      const finderInput = overlay.querySelector('[data-nav-find-input]');
      const finderBtn = overlay.querySelector('[data-nav-find-btn]');

      function goFinder() {
        const base = data.searchUrl;
        if (!base) return;
        const q = (finderInput?.value || '').trim();
        window.location.href = q ? `${base}?q=${encodeURIComponent(q)}` : base;
      }

      finderBtn?.addEventListener('click', goFinder);
      finderInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') goFinder();
      });

      elList.querySelectorAll('.nav-overlay__item').forEach((row) => {
        row.addEventListener('mouseenter', () => {
          state.idx = Number(row.getAttribute('data-idx') || '0');
          elList.querySelectorAll('.nav-overlay__item').forEach(x => x.classList.remove('is-active'));
          row.classList.add('is-active');
          renderRight();
        });

        row.addEventListener('click', () => {
          const idx = Number(row.getAttribute('data-idx') || '0');
          const item = NAV_DATA[state.key]?.items?.[idx];
          if (item?.url) window.location.href = item.url;
        });
      });

      // Make list scrollable
      elList.classList.add('nav-overlay__list--scroll');
      elList.addEventListener('scroll', setThumbFromScroll, { passive: true });
      setThumbFromScroll();
    }

    function open(key) {
      const data = NAV_DATA[key];
      if (!data) return;

      state.key = key;
      state.idx = data.defaultIndex ?? 0;

      overlay.classList.remove('hidden');
      overlay.setAttribute('aria-hidden', 'false');
      document.body.classList.add('nav-overlay-open'); // used to hide header
      lockScroll(true);

      renderLeft();
      renderRight();

      if (data.showSearch) setTimeout(() => searchInput?.focus(), 10);
    }

    function close() {
      overlay.classList.add('hidden');
      overlay.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('nav-overlay-open');
      lockScroll(false);
      state.key = null;
      state.idx = 0;
    }

    // Bind header links
    document.querySelectorAll('[data-overlay-key]').forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        open(a.getAttribute('data-overlay-key'));
      });
    });

    btnClose?.addEventListener('click', close);

    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) close();
    });

    document.addEventListener('keydown', (e) => {
      const isOpen = !overlay.classList.contains('hidden');
      if (!isOpen) return;
      if (e.key === 'Escape') {
        e.preventDefault();
        close();
      }
    });

    function goSearch() {
      const data = NAV_DATA[state.key];
      if (!data?.searchUrl) return;
      const q = (searchInput?.value || '').trim();
      if (q) window.location.href = `${data.searchUrl}?q=${encodeURIComponent(q)}`;
      else window.location.href = data.searchUrl;
    }
    searchBtn?.addEventListener('click', goSearch);
    searchInput?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') goSearch();
    });
  })();
});