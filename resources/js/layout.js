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
      if (hintEl) hintEl.textContent = hintEl.dataset.hintDefault || 'Type at least 3 characters…';
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
    try {
      const locale = document.documentElement.lang || 'en';
      console.log('🔍 Searching for:', q, 'locale:', locale);

      const url = `/${locale}/search?q=${encodeURIComponent(q)}`;
      console.log('📡 Fetch URL:', url);

      const res = await fetch(url, {
        headers: { Accept: 'application/json' },
      });

      console.log('📋 Response status:', res.status, res.statusText);

      if (!res.ok) {
        console.error('❌ Response not OK:', res.statusText);
        hintEl.textContent = `Error: ${res.status} ${res.statusText}`;
        resultsEl.innerHTML = '';
        activeIndex = -1;
        return;
      }

      const json = await res.json();
      console.log('📦 Response JSON:', json);

      const items = json.results || [];
      console.log('✅ Items found:', items.length);

      if (!items.length) {
        hintEl.textContent = hintEl.dataset.hintNoResults || 'No results found.';
        resultsEl.innerHTML = '';
        activeIndex = -1;
        return;
      }

      resultsEl.innerHTML = items
        .map((item, idx) => {
          const typeIcon = {
            'Product': '📦',
            'Industry': '🏭',
            'News': '📰',
            'Page': '📄',
            'Shortcut': '⚡'
          }[item.type] || '•';

          const img = item.image
            ? `<img class="search-result__img" src="${esc(item.image)}" alt="" loading="lazy">`
            : `<div class="search-result__img" title="${esc(item.type)}">${typeIcon}</div>`;

          return `
            <a class="search-result" href="${esc(item.url)}" data-idx="${idx}" tabindex="-1">
              ${img}
              <div class="search-result__content">
                <div class="search-result__title">${esc(item.title)}</div>
                <div class="search-result__meta">${esc(item.type)}</div>
              </div>
            </a>
          `;
        })
        .join('');

      hintEl.textContent = `${items.length} result${items.length !== 1 ? 's' : ''} found`;
      activeIndex = -1;
      setActiveResult(0);
      console.log('✨ Search complete');
    } catch (error) {
      console.error('💥 Search error:', error);
      console.error('Stack:', error.stack);
      hintEl.textContent = `Error: ${error.message}`;
      resultsEl.innerHTML = '';
      activeIndex = -1;
    }
  }

  input?.addEventListener('input', () => {
    const q = input.value.trim();
    clearTimeout(timer);

    if (q.length === 0) {
      hintEl.textContent = hintEl.dataset.hintDefault || 'Enter search term…';
      resultsEl.innerHTML = '';
      activeIndex = -1;
      return;
    }

    if (q.length < 3) {
      const remaining = 3 - q.length;
      hintEl.textContent = `Type ${remaining} more character${remaining !== 1 ? 's' : ''}…`;
      resultsEl.innerHTML = '';
      activeIndex = -1;
      return;
    }

    hintEl.textContent = hintEl.dataset.hintSearching || 'Searching…';
    timer = setTimeout(() => doSearch(q), 200);
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

      const kicker    = (s.kicker     || '').trim();
      const title     = (s.title      || '').trim();
      const lead      = (s.lead       || '').trim();
      const cta1Label = (s.cta1_label || '').trim();
      const cta1Url   = (s.cta1_url   || '').trim();
      const cta2Label = (s.cta2_label || '').trim();
      const cta2Url   = (s.cta2_url   || '').trim();

      if (kickerEl) {
          kickerEl.textContent = kicker;
          kickerEl.classList.toggle('hidden', !kicker);
      }
      if (titleEl) titleEl.textContent = title || '';
      if (leadEl) {
          leadEl.textContent = lead;
          leadEl.classList.toggle('hidden', !lead);
      }
      if (ctaWrap) {
          ctaWrap.innerHTML = '';
          if (cta1Label && cta1Url) {
              const a1 = document.createElement('a');
              a1.href = cta1Url;
              a1.textContent = cta1Label;
              a1.className = 'rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/50';
              ctaWrap.appendChild(a1);
          }
          if (cta2Label && cta2Url) {
              const a2 = document.createElement('a');
              a2.href = cta2Url;
              a2.textContent = cta2Label;
              a2.className = 'rounded-md border border-white/30 px-5 py-2.5 font-medium hover:bg-white/50';
              ctaWrap.appendChild(a2);
          }
          ctaWrap.classList.toggle('hidden', !cta1Label && !cta2Label);
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
    const elListWrap  = document.getElementById('navOverlayListWrap');
    const elDesc = document.getElementById('navOverlayDesc');
    const elPreview = document.getElementById('navOverlayPreview');

    /*const searchWrap = document.getElementById('navOverlaySearchWrap');
    const searchInput = document.getElementById('navOverlaySearchInput');
    const searchBtn = document.getElementById('navOverlaySearchBtn');*/
    const thumbTrack = document.getElementById('navOverlayThumbTrack');
    const thumb = document.getElementById('navOverlayThumb');

    if (!overlay || !elTitle || !elList || !elDesc || !elPreview) return;

    const locale = document.documentElement.lang || 'en';
    const NAV_SCROLL_THUMB_MIN_H = 42;

    // NOTE: update these URLs/images whenever you create pages
    /*const NAV_DATA = {
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
    };*/

    const NAV_DATA = (function () {
      const el = document.getElementById('gt-nav-data');
      if (!el) return {};
      let groups = [];
      try { groups = JSON.parse(el.textContent || '[]'); } catch { groups = []; }

      const locale = document.documentElement.lang || 'en';
      const fallback = 'en';

      const pick = (obj) => {
        if (!obj) return '';
        if (typeof obj === 'string') return obj;
        if (typeof obj !== 'object') return '';
        return obj[locale] || obj[fallback] || Object.values(obj)[0] || '';
      };

      const sanitizeUrl = (url) => {
        const v = String(url || '').trim();
        if (!v) return '#';
        if (v.startsWith('/')) return v;
        if (/^https?:\/\//i.test(v)) return v;
        return '#';
      };

      const resolveUrl = (it) => {
        if (it.page_slug) return `/${locale}/pages/${it.page_slug}`;
        const u = it.url || '#';
        return sanitizeUrl(u.replace('{locale}', locale));
      };

      const out = {};
      (groups || []).forEach((g) => {
        const key = g.key;
        if (!key) return;

        const overlayUrl = (() => {
          if (g.overlay_slug) return `/${locale}/pages/${g.overlay_slug}`;
          const raw = (g.overlay_url || '').trim();
          if (!raw) return null;
          return sanitizeUrl(raw.replace('{locale}', locale));
        })();

        out[key] = {
          title: pick(g.label),
          overlayUrl,
          showSearch: key === 'products', // keep same behavior
          searchUrl: `/${locale}/products`,
          defaultIndex: 0,
          items: (g.links || []).map((l) => ({
            title: pick(l.label),
            url: resolveUrl(l),
            desc: l.desc || '',
            previewImage: (function () {
                const raw = (l.preview_image || l.previewImage || '').trim();
                if (!raw) return '';
                if (raw.startsWith('http://') || raw.startsWith('https://')) return raw;
                if (raw.startsWith('/')) return raw;
                // Storage disk path uploaded via FileUpload (e.g. "nav-previews/abc123.jpg")
                if (raw.includes('/') || /\.[a-z]{2,5}$/i.test(raw)) return `/storage/${raw}`;
                // Legacy: bare filename only → fallback to overlay folder
                return `/images/overlay/${raw}`;
            })(),
            isFinder: !!l.is_finder || l.action === 'finder',
            target: l.target || '_self',
          })),
        };
      });

      return out;
    })();
    
    let state = {
      key: null,
      idx: 0,
    };
    let thumbResizeBound = false;

    const isMobileNav = () => window.innerWidth < 768;
    const elGroupTabs = document.getElementById('navOverlayGroupTabs');
    const elSublevel  = document.getElementById('navOverlaySublevel');
    const elSubBack   = document.getElementById('navOverlaySubBack');
    const elSubTitle  = document.getElementById('navOverlaySubTitle');
    const elSubList   = document.getElementById('navOverlaySubList');

    function lockScroll(lock) {
      document.documentElement.classList.toggle('overflow-hidden', lock);
      document.body.classList.toggle('overflow-hidden', lock);
    }

    function clearChildren(el) {
      while (el.firstChild) el.removeChild(el.firstChild);
    }

    function setThumbFromScroll() {
      if (!thumbTrack || !thumb || !elList) return;
      const listHeight = elList.clientHeight;
      const scrollHeight = elList.scrollHeight;
      const trackHeight = thumbTrack.clientHeight;
      if (!listHeight || !scrollHeight || !trackHeight) return;

      if (scrollHeight <= listHeight + 1) {
        thumb.style.height = `${trackHeight}px`;
        thumb.style.top = '0px';
        return;
      }

      const ratio = listHeight / scrollHeight;
      const thumbHeight = Math.max(NAV_SCROLL_THUMB_MIN_H, Math.round(trackHeight * ratio));
      const maxTop = trackHeight - thumbHeight;
      const scrollRatio = elList.scrollTop / Math.max(1, scrollHeight - listHeight);
      const top = Math.round(maxTop * scrollRatio);

      thumb.style.height = `${thumbHeight}px`;
      thumb.style.top = `${top}px`;
    }

    function setActiveRow(nextIdx) {
      state.idx = Math.max(0, nextIdx);
      elList.querySelectorAll('.nav-overlay__item').forEach((x, i) => {
        x.classList.toggle('is-active', i === state.idx);
      });
      const current = elList.querySelector(`.nav-overlay__item[data-idx="${state.idx}"]`);
      current?.scrollIntoView({ block: 'nearest' });
      renderRight();
    }

    function goItem(item) {
      if (!item?.url || item.url === '#') return;
      if (item.target === '_blank') window.open(item.url, '_blank', 'noopener');
      else window.location.href = item.url;
    }

    function buildGroupTabs() {
        if (!elGroupTabs) return;
        clearChildren(elGroupTabs);
        Object.entries(NAV_DATA).forEach(([key, data]) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'nav-overlay__groupTab' + (state.key === key ? ' is-active' : '');
            btn.textContent = data.title || key;
            btn.dataset.groupKey = key;
            btn.addEventListener('click', () => switchGroup(key));
            elGroupTabs.appendChild(btn);
        });
    }

    function switchGroup(key) {
        const data = NAV_DATA[key];
        if (!data) return;
        state.key = key;
        state.idx = data.defaultIndex ?? 0;
        buildGroupTabs();
        renderLeft();
        renderRight();
    }

    function buildMobileGroupList() {
        clearChildren(elList);
        elTitle.textContent = i18n.menu || 'Menu';
        Object.entries(NAV_DATA).forEach(([key, data]) => {
            const rowWrap = document.createElement('div');
            rowWrap.className = 'nav-overlay__row';
            const item = document.createElement('div');
            item.className = 'nav-overlay__item';
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', '0');
            item.dataset.groupKey = key;
            const title = document.createElement('div');
            title.className = 'nav-overlay__itemTitle';
            title.textContent = data.title || key;
            const chev = document.createElement('div');
            chev.className = 'nav-overlay__chev';
            chev.textContent = '›';
            item.appendChild(title);
            item.appendChild(chev);
            const divider = document.createElement('div');
            divider.className = 'nav-overlay__divider';
            rowWrap.appendChild(item);
            rowWrap.appendChild(divider);
            elList.appendChild(rowWrap);
            item.addEventListener('click', () => openSubLevel(key));
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openSubLevel(key); }
            });
        });
    }

    function openSubLevel(key) {
        const data = NAV_DATA[key];
        if (!data || !elSublevel || !elSubTitle || !elSubList) return;
        state.key = key;
        elSubTitle.textContent = data.title || key;
        clearChildren(elSubList);
        (data.items || []).forEach((it) => {
            const rowWrap = document.createElement('div');
            rowWrap.className = 'nav-overlay__row';
            const item = document.createElement(it.url && it.url !== '#' ? 'a' : 'div');
            item.className = 'nav-overlay__subItem';
            if (it.url && it.url !== '#') {
                item.href = it.url;
                if (it.target === '_blank') { item.target = '_blank'; item.rel = 'noopener'; }
            }
            const label = document.createElement('div');
            label.className = 'nav-overlay__itemTitle';
            label.textContent = it.title || '';
            const chev = document.createElement('div');
            chev.className = 'nav-overlay__chev';
            chev.textContent = '›';
            item.appendChild(label);
            item.appendChild(chev);
            const divider = document.createElement('div');
            divider.className = 'nav-overlay__divider';
            rowWrap.appendChild(item);
            rowWrap.appendChild(divider);
            elSubList.appendChild(rowWrap);
        });
        // ✦ Trigger bilateral slide via wrapper class
        elListWrap?.classList.add('sublevel-open');
        elSublevel.setAttribute('aria-hidden', 'false');
        // Reset sublevel scroll to top
        elSublevel.scrollTop = 0;
    }

    function closeSubLevel() {
        elListWrap?.classList.remove('sublevel-open');
        elSublevel?.setAttribute('aria-hidden', 'true');
    }

    function renderRight() {
      const data = NAV_DATA[state.key];
      if (!data) return;
      const item = data.items?.[state.idx] || data.items?.[0];
      if (!item) return;

      elDesc.textContent = item.desc || '';
      const img = item.previewImage || item.preview_image || '';
      clearChildren(elPreview);
      if (img) {
        const image = document.createElement('img');
        image.src = img;
        image.alt = item.title || '';
        elPreview.appendChild(image);
      }
    }

    const navOverlayContent = document.querySelector('.nav-overlay__content');

    const i18n = {
        menu:   navOverlayContent?.dataset.i18nMenu   || 'Menu',
        what:   navOverlayContent?.dataset.i18nWhat   || 'What are you looking for?',
        cancel: navOverlayContent?.dataset.i18nCancel || 'Cancel',
        leave:  navOverlayContent?.dataset.i18nLeave  || 'Leave page',
    };

    function renderLeft() {
      const data = NAV_DATA[state.key];
      if (!data) return;
 
      // Section title: plain text OR a link, depending on admin config
      clearChildren(elTitle);
      if (data.overlayUrl) {
        const a = document.createElement('a');
        a.href = data.overlayUrl;
        a.textContent = data.title || 'Menu';
        a.className = 'nav-overlay__sectionTitle--link';
        if (data.overlayTarget === '_blank') { a.target = '_blank'; a.rel = 'noopener'; }
        elTitle.appendChild(a);
      } else {
        elTitle.textContent = data.title || 'Menu';
      }
      clearChildren(elList);

      (data.items || []).forEach((it, i) => {
        const rowWrap = document.createElement('div');
        rowWrap.className = `nav-overlay__row ${it.isFinder ? 'is-finder' : ''}`;

        const item = document.createElement('div');
        item.className = `nav-overlay__item ${i === state.idx ? 'is-active' : ''}`;
        item.setAttribute('data-idx', String(i));

        const title = document.createElement('div');
        title.className = 'nav-overlay__itemTitle';
        title.textContent = it.title || '';

        const chev = document.createElement('div');
        chev.className = 'nav-overlay__chev';
        chev.textContent = '›';

        item.appendChild(title);
        item.appendChild(chev);
        rowWrap.appendChild(item);

        if (it.isFinder) {
          const finder = document.createElement('div');
          finder.className = 'nav-overlay__finder';

          const finderInput = document.createElement('input');
          finderInput.type = 'text';
          finderInput.className = 'nav-overlay__finderInput';
          finderInput.placeholder = i18n.what;
          finderInput.setAttribute('data-nav-find-input', '1');

          const finderBtn = document.createElement('button');
          finderBtn.type = 'button';
          finderBtn.className = 'nav-overlay__finderBtn';
          finderBtn.setAttribute('aria-label', 'Search');
          finderBtn.setAttribute('data-nav-find-btn', '1');
          finderBtn.textContent = '⌕';

          const goFinder = () => {
            const base = data.searchUrl;
            if (!base) return;
            const q = (finderInput.value || '').trim();
            window.location.href = q ? `${base}?q=${encodeURIComponent(q)}` : base;
          };

          finderBtn.addEventListener('click', goFinder);
          finderInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') goFinder();
          });

          finder.appendChild(finderInput);
          finder.appendChild(finderBtn);
          rowWrap.appendChild(finder);
        }

      /*// Search only for products; placed under title, like BASF
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
      }*/
        const divider = document.createElement('div');
        divider.className = 'nav-overlay__divider';
        rowWrap.appendChild(divider);

      /*finderBtn?.addEventListener('click', goFinder);
      finderInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') goFinder();
      });*/
        item.addEventListener('mouseenter', () => setActiveRow(i));
        item.addEventListener('click', () => goItem(NAV_DATA[state.key]?.items?.[i]));

      /*elList.querySelectorAll('.nav-overlay__item').forEach((row) => {
        row.addEventListener('mouseenter', () => {
          state.idx = Number(row.getAttribute('data-idx') || '0');
          elList.querySelectorAll('.nav-overlay__item').forEach(x => x.classList.remove('is-active'));
          row.classList.add('is-active');
          renderRight();
        });

        row.addEventListener('click', () => {
          const idx = Number(row.getAttribute('data-idx') || '0');
          const item = NAV_DATA[state.key]?.items?.[idx];
          if (item?.url) {
            if (item.target === '_blank') window.open(item.url, '_blank', 'noopener');
            else window.location.href = item.url;
          }
        });*/
        elList.appendChild(rowWrap);
      });

      // Make list scrollable
      elList.classList.add('nav-overlay__list--scroll');
      elList.addEventListener('wheel', (e) => {
        // If list can scroll, consume the wheel so the page/body doesn't.
        const canScroll = elList.scrollHeight > elList.clientHeight + 1;
        if (!canScroll) return;

        // Prevent the event from being treated as body scroll
        e.preventDefault();
        elList.scrollTop += e.deltaY;
      }, { passive: false });
      elList.addEventListener('scroll', setThumbFromScroll, { passive: true });
      if (!thumbResizeBound) {
        window.addEventListener('resize', setThumbFromScroll, { passive: true });
        thumbResizeBound = true;
      }
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

      buildGroupTabs();
      if (isMobileNav()) {
          buildMobileGroupList();
      } else {
          renderLeft();
          renderRight();
      }

      /*if (data.showSearch) setTimeout(() => searchInput?.focus(), 10);*/
    }

    function close() {
      overlay.classList.add('hidden');
      overlay.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('nav-overlay-open');
      lockScroll(false);
      elListWrap?.classList.remove('sublevel-open'); // ← ensure clean state
      closeSubLevel();
      state.key = null;
      state.idx = 0;
    }

    /*function readNavData() {
      const el = document.getElementById('gt-nav-data');
      if (!el) return [];
      try { return JSON.parse(el.textContent || '[]'); } catch { return []; }
    }

    function pickLabel(obj, locale, fallback) {
      if (!obj) return '';
      if (typeof obj === 'string') return obj;
      if (typeof obj !== 'object') return '';
      return obj[locale] || obj[fallback] || Object.values(obj)[0] || '';
    }

    function resolveNavUrl(item, locale) {
      if (item.page_slug) return `/${locale}/pages/${item.page_slug}`;
      const u = item.url || '#';
      return u.replace('{locale}', locale);
    }*/

    // Bind header links
    document.querySelectorAll('[data-overlay-key]').forEach((a) => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        const key = a.getAttribute('data-overlay-key');
        if (!overlay.classList.contains('hidden')) {
            switchGroup(key);
        } else {
            open(key);
        }
      });
    });

    btnClose?.addEventListener('click', close);

    elSubBack?.addEventListener('click', closeSubLevel);
    window.addEventListener('resize', () => {
        if (!overlay || overlay.classList.contains('hidden')) return;
        buildGroupTabs();
    }, { passive: true });

    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) close();
    });

    document.addEventListener('keydown', (e) => {
      const isOpen = !overlay.classList.contains('hidden');
      if (!isOpen) return;
      if (e.key === 'Escape') {
        e.preventDefault();
        close();
        return;
      }
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        const data = NAV_DATA[state.key];
        if (!data?.items?.length) return;
        setActiveRow((state.idx + 1) % data.items.length);
        return;
      }
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        const data = NAV_DATA[state.key];
        if (!data?.items?.length) return;
        setActiveRow((state.idx - 1 + data.items.length) % data.items.length);
        return;
      }
      if (e.key === 'Enter') {
        e.preventDefault();
        goItem(NAV_DATA[state.key]?.items?.[state.idx]);
      }
    /*});

    function goSearch() {
      const data = NAV_DATA[state.key];
      if (!data?.searchUrl) return;
      const q = (searchInput?.value || '').trim();
      if (q) window.location.href = `${data.searchUrl}?q=${encodeURIComponent(q)}`;
      else window.location.href = data.searchUrl;
    }
    searchBtn?.addEventListener('click', goSearch);
    searchInput?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') goSearch();*/
    });
  })();
});