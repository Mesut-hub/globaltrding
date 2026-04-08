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
  // Cookie consent + GA4 (consent gated)
  const GA_ID = 'G-HLE66GHDML';
  const KEY = 'cookie_consent_v1'; // values: 'accepted' | 'rejected'

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

  const existing = localStorage.getItem(KEY);

  if (existing === 'accepted') {
    loadGA();
  } else if (existing === 'rejected') {
    // do nothing
  } else {
    banner?.classList.remove('hidden');
  }

  acceptBtn?.addEventListener('click', () => {
    localStorage.setItem(KEY, 'accepted');
    banner?.classList.add('hidden');
    loadGA();
  });

  rejectBtn?.addEventListener('click', () => {
    localStorage.setItem(KEY, 'rejected');
    banner?.classList.add('hidden');
  });

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

  // ─── Nav Overlay (BASF-style mega menu) ─────────────────────────────────────
  const navOverlay     = document.getElementById('navOverlay');
  const navOverlayClose= document.getElementById('navOverlayClose');
  const navOverlayList = document.getElementById('navOverlayList');
  const navScrollThumb = document.getElementById('navOverlayScrollThumb');
  const navSearchWrap  = document.getElementById('navOverlaySearch');
  const navSearchInput = document.getElementById('navOverlaySearchInput');
  const navPreviewImg  = document.getElementById('navOverlayPreviewImg');
  const navPreviewTitle= document.getElementById('navOverlayPreviewTitle');
  const navPreviewDesc = document.getElementById('navOverlayPreviewDesc');

  /** Minimum height (px) for the scroll thumb — must match CSS min-height */
  const NAV_SCROLL_THUMB_MIN_H = 32;

  let activeOverlayKey = null;

  function openNavOverlay(triggerEl) {
    if (!navOverlay) return;

    const key   = triggerEl.getAttribute('data-overlay-key');
    const title = triggerEl.getAttribute('data-overlay-title') || '';
    const desc  = triggerEl.getAttribute('data-overlay-desc')  || '';
    let   items = [];

    try { items = JSON.parse(triggerEl.getAttribute('data-overlay-items') || '[]'); }
    catch (e) { items = []; }

    activeOverlayKey = key;

    // Fill preview text
    if (navPreviewTitle) navPreviewTitle.textContent = title;
    if (navPreviewDesc)  navPreviewDesc.textContent  = desc;

    // Preview image: hide until explicitly set (no default image, graceful)
    if (navPreviewImg) {
      navPreviewImg.src = '';
      navPreviewImg.classList.remove('is-loaded');
      navPreviewImg.style.display = 'none';
    }

    // Build nav list
    if (navOverlayList) {
      // Clear list safely without innerHTML
      while (navOverlayList.firstChild) navOverlayList.removeChild(navOverlayList.firstChild);

      // For products: show search after the Product Finder item
      const isProducts = key === 'products';

      items.forEach((item) => {
        const a = document.createElement('a');
        a.href = item.href || '#';
        a.className = 'nav-overlay__list-item' + (item.isProductFinder ? ' is-finder' : '');
        a.textContent = item.label || '';
        navOverlayList.appendChild(a);

        // Insert search input right after Product Finder (products only)
        if (isProducts && item.isProductFinder && navSearchWrap) {
          navSearchWrap.style.display = '';
          navSearchWrap.setAttribute('aria-hidden', 'false');
          navOverlayList.appendChild(navSearchWrap);
        }
      });

      // Hide search for non-products sections
      if (!isProducts && navSearchWrap) {
        navSearchWrap.style.display = 'none';
        navSearchWrap.setAttribute('aria-hidden', 'true');
      }

      // Scroll indicator tracking
      syncScrollThumb();
      navOverlayList.addEventListener('scroll', syncScrollThumb, { passive: true });
    }

    // Open
    navOverlay.classList.add('is-open');
    navOverlay.setAttribute('aria-hidden', 'false');
    lockBody(true);

    // Focus search if products, else close button
    if (key === 'products' && navSearchInput) {
      setTimeout(() => navSearchInput.focus(), 80);
    } else {
      setTimeout(() => navOverlayClose?.focus(), 80);
    }
  }

  function closeNavOverlay() {
    if (!navOverlay) return;
    navOverlay.classList.remove('is-open');
    navOverlay.setAttribute('aria-hidden', 'true');
    activeOverlayKey = null;
    lockBody(false);
  }

  function syncScrollThumb() {
    if (!navScrollThumb || !navOverlayList) return;
    const el    = navOverlayList;
    const track = navScrollThumb.parentElement;
    if (!track) return;

    const ratio = el.scrollHeight > el.clientHeight
      ? el.scrollTop / (el.scrollHeight - el.clientHeight)
      : 0;

    const thumbH   = Math.max(NAV_SCROLL_THUMB_MIN_H, track.clientHeight * (el.clientHeight / Math.max(el.scrollHeight, 1)));
    const thumbTop = ratio * (track.clientHeight - thumbH);

    navScrollThumb.style.height = `${thumbH}px`;
    navScrollThumb.style.top    = `${thumbTop}px`;
  }

  // Click on nav items with data-overlay-key
  document.addEventListener('click', (e) => {
    // Backdrop click: closes overlay (check first so inner clicks don't reach here)
    if (e.target === navOverlay) {
      closeNavOverlay();
      return;
    }

    // Clicks inside the overlay body are handled by event targets within it
    if (e.target.closest('.nav-overlay__body')) return;

    // Nav trigger clicked
    const trigger = e.target.closest('[data-overlay-key]');
    if (trigger) {
      e.preventDefault();
      // Toggle: clicking the same section closes it; a different section opens
      if (activeOverlayKey === trigger.getAttribute('data-overlay-key')) {
        closeNavOverlay();
      } else {
        openNavOverlay(trigger);
      }
    }
  });

  navOverlayClose?.addEventListener('click', closeNavOverlay);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && activeOverlayKey !== null) {
      e.preventDefault();
      closeNavOverlay();
    }
  });
});