/**
 * GLOBALTRDING — RESPONSIVE JS ENHANCEMENTS v2.0
 * Mobile navigation, touch gestures, adaptive layout helpers
 */

(function () {
  'use strict';

  /* ── Utility helpers ─────────────────────────────────── */
  const qs  = (sel, ctx = document) => ctx.querySelector(sel);
  const qsa = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
  const on  = (el, ev, fn, opts) => el && el.addEventListener(ev, fn, opts);

  /* ── 1. VIEWPORT HEIGHT FIX (iOS/Android 100svh fallback) */
  function setVH() {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty('--vh', `${vh}px`);
  }
  setVH();
  on(window, 'resize', setVH, { passive: true });
  on(window, 'orientationchange', () => setTimeout(setVH, 100), { passive: true });

  /* ── 2. MOBILE HAMBURGER MENU ────────────────────────── */
  function initMobileMenu() {
    const header   = qs('#siteHeader');
    const overlay  = qs('#navOverlay');
    if (!header || !overlay) return;

    // Inject hamburger btn if not present
    const icons = qs('.header-icons', header);
    if (!icons || qs('.mobile-menu-btn', icons)) return;

    const btn = document.createElement('button');
    btn.className = 'mobile-menu-btn';
    btn.setAttribute('aria-label', 'Open menu');
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-controls', 'navOverlay');
    btn.innerHTML = '&#9776;'; // ☰
    btn.type = 'button';

    icons.prepend(btn);

    // Wire to existing nav overlay open logic
    on(btn, 'click', () => {
      // Trigger first nav group or generic open
      const firstKey = qs('[data-overlay-key]');
      if (firstKey) {
        firstKey.click();
      } else {
        overlay.classList.remove('hidden');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('nav-overlay-open');
        document.documentElement.classList.add('overflow-hidden');
        document.body.classList.add('overflow-hidden');
      }
      btn.setAttribute('aria-expanded', 'true');
    });

    // Update aria when overlay closes
    const closeBtn = qs('#navOverlayClose');
    if (closeBtn) {
      on(closeBtn, 'click', () => btn.setAttribute('aria-expanded', 'false'));
    }
    on(document, 'keydown', (e) => {
      if (e.key === 'Escape') btn.setAttribute('aria-expanded', 'false');
    });
  }

  /* ── 3. RESPONSIVE HEADER — SHOW/HIDE ON SCROLL ─────── */
  function initSmartHeader() {
    const header = qs('#siteHeader');
    if (!header) return;

    let lastY = 0;
    let ticking = false;
    const THRESHOLD = 60;

    on(window, 'scroll', () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          const y = window.scrollY;
          const isMobile = window.innerWidth <= 1100;

          if (isMobile) {
            // Hide on scroll down after threshold, show on scroll up
            if (y > lastY && y > THRESHOLD) {
              header.style.transform = 'translateY(-100%)';
              header.style.transition = 'transform 0.3s ease';
            } else if (y < lastY) {
              header.style.transform = 'translateY(0)';
            }
          } else {
            header.style.transform = '';
          }

          lastY = y;
          ticking = false;
        });
        ticking = true;
      }
    }, { passive: true });

    // Always show when nav overlay is open
    on(document.body, 'classchange', () => {
      if (document.body.classList.contains('nav-overlay-open')) {
        header.style.transform = 'translateY(0)';
      }
    });
  }

  /* ── 4. TOUCH SWIPE FOR CAROUSELS ───────────────────── */
  function initSwipeCarousels() {
    const tracks = qsa('[data-carousel-track], [data-ind="track"]');

    tracks.forEach(track => {
      let startX = 0;
      let startScrollLeft = 0;
      let isDragging = false;

      on(track, 'touchstart', e => {
        startX = e.touches[0].pageX;
        startScrollLeft = track.scrollLeft;
        isDragging = true;
      }, { passive: true });

      on(track, 'touchmove', e => {
        if (!isDragging) return;
        const dx = startX - e.touches[0].pageX;
        track.scrollLeft = startScrollLeft + dx;
      }, { passive: true });

      on(track, 'touchend', () => { isDragging = false; }, { passive: true });

      // Mouse drag for desktop
      on(track, 'mousedown', e => {
        startX = e.pageX - track.offsetLeft;
        startScrollLeft = track.scrollLeft;
        isDragging = true;
        track.style.cursor = 'grabbing';
        track.style.userSelect = 'none';
      });

      on(document, 'mouseup', () => {
        isDragging = false;
        track.style.cursor = '';
        track.style.userSelect = '';
      });

      on(document, 'mousemove', e => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        track.scrollLeft = startScrollLeft - (x - startX);
      });
    });
  }

  /* ── 5. RESPONSIVE IMAGES — LAZY LOADING OBSERVER ───── */
  function initLazyImages() {
    if (!('IntersectionObserver' in window)) return;

    const imgs = qsa('img[data-src], img[loading="lazy"]');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
        }
        img.classList.add('img-loaded');
        io.unobserve(img);
      });
    }, { rootMargin: '200px 0px' });

    imgs.forEach(img => io.observe(img));
  }

  /* ── 6. FILAMENT TABLE — RESPONSIVE LABELS ───────────── */
  function initResponsiveTables() {
    function labelCells() {
      const tables = qsa('.fi-ta-table, table[data-responsive]');
      tables.forEach(table => {
        const headers = qsa('thead th', table).map(th => th.textContent.trim());
        qsa('tbody tr', table).forEach(row => {
          qsa('td', row).forEach((td, i) => {
            if (headers[i]) td.setAttribute('data-label', headers[i]);
          });
        });
      });
    }

    // Run on DOMContentLoaded and after any Livewire updates
    labelCells();
    document.addEventListener('livewire:load', labelCells);
    document.addEventListener('livewire:update', labelCells);
  }

  /* ── 7. COLLAPSIBLE FILTER PANEL (mobile) ────────────── */
  function initFilterToggle() {
    if (window.innerWidth > 860) return;

    const filtersForm = qs('.gt-pf__filtersForm');
    if (!filtersForm) return;

    const wrapper = filtersForm.closest('.gt-pf__filters');
    if (!wrapper) return;

    // Already has a toggle
    if (wrapper.querySelector('.filter-toggle-btn')) return;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'filter-toggle-btn gt-btn';
    btn.style.cssText = 'width:100%;margin-bottom:12px;background:#0f172a;color:#fff;border:0;padding:12px;font-weight:700;border-radius:8px;display:flex;align-items:center;justify-content:space-between;';
    btn.innerHTML = `<span>Filters</span><span class="filter-toggle-icon">▼</span>`;

    // Collapse form by default on mobile
    filtersForm.style.display = 'none';

    wrapper.insertBefore(btn, filtersForm);

    on(btn, 'click', () => {
      const isOpen = filtersForm.style.display !== 'none';
      filtersForm.style.display = isOpen ? 'none' : 'grid';
      btn.querySelector('.filter-toggle-icon').textContent = isOpen ? '▼' : '▲';
      btn.setAttribute('aria-expanded', String(!isOpen));
    });
  }

  /* ── 8. FLOATING ACTION BUTTON — SCROLL TOP ──────────── */
  function initScrollTop() {
    const btn = qs('[data-scroll-top]');
    if (!btn) return;

    on(window, 'scroll', () => {
      btn.style.opacity = window.scrollY > 400 ? '1' : '0';
      btn.style.pointerEvents = window.scrollY > 400 ? 'auto' : 'none';
    }, { passive: true });

    on(btn, 'click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  /* ── 9. ADAPTIVE BREAKPOINT EVENTS ───────────────────── */
  const bps = {
    mobile:  window.matchMedia('(max-width: 479px)'),
    tablet:  window.matchMedia('(min-width: 480px) and (max-width: 1099px)'),
    desktop: window.matchMedia('(min-width: 1100px)'),
  };

  function onBreakpoint(bp, fn) {
    const mq = bps[bp];
    if (!mq) return;
    fn(mq.matches);
    mq.addEventListener('change', e => fn(e.matches));
  }

  // Expose globally
  window.gtBreakpoint = { onBreakpoint, bps };

  // Re-init filter toggle on resize
  onBreakpoint('mobile', (isMobile) => {
    if (isMobile) initFilterToggle();
  });

  onBreakpoint('tablet', (isTablet) => {
    if (isTablet) initFilterToggle();
  });

  /* ── 10. RESPONSIVE NAV (mark active overlay link) ───── */
  function markActiveNavLink() {
    const path = window.location.pathname;
    qsa('[data-overlay-key]').forEach(a => {
      const key = a.getAttribute('data-overlay-key');
      // Basic: if path includes key segment, mark active
      const segment = '/' + key.replace(/-/g, '-');
      if (path.includes(key)) {
        a.classList.add('is-current');
        a.setAttribute('aria-current', 'page');
      }
    });
  }

  /* ── 11. TOUCH: PREVENT DOUBLE-TAP ZOOM ON BUTTONS ──── */
  function preventDoubleTapZoom() {
    let lastTap = 0;
    on(document, 'touchend', e => {
      const now = Date.now();
      if (now - lastTap < 300) {
        const el = e.target.closest('button, a, [role="button"]');
        if (el) e.preventDefault();
      }
      lastTap = now;
    }, { passive: false });
  }

  /* ── 12. SKIP TO MAIN CONTENT (a11y) ─────────────────── */
  function injectSkipLink() {
    if (qs('.skip-to-main')) return;
    const skip = document.createElement('a');
    skip.className = 'skip-to-main';
    skip.href = '#main-content';
    skip.textContent = 'Skip to main content';
    document.body.prepend(skip);

    // Add id to main if missing
    const main = qs('main.site-main, [role="main"]');
    if (main && !main.id) main.id = 'main-content';
  }

  /* ── 13. RESPONSIVE HERO OFFSET (recompute on resize) ── */
  function initHeroOffset() {
    const heros = qsa('[data-hero]');
    heros.forEach(hero => {
      const content = qs('[data-hero-content]', hero);
      if (!content) return;

      function applyOffset() {
        const isMobile = window.innerWidth <= 900;
        if (isMobile) {
          content.style.transform   = 'translate(20px, 0)';
          content.style.maxWidth    = 'calc(100vw - 40px)';
        } else {
          // Restore inline transform from data attribute
          const slides = content.dataset.heroSlides;
          // Let CSS handle it via inline style from Blade
        }
      }

      applyOffset();
      on(window, 'resize', applyOffset, { passive: true });
    });
  }

  /* ── 14. LANG MENU — CLOSE ON OUTSIDE TAP (mobile) ──── */
  function initLangMenuA11y() {
    on(document, 'touchstart', e => {
      const openDD = qs('[data-lang-dropdown][data-open="1"]');
      if (!openDD) return;
      if (!openDD.contains(e.target)) {
        openDD.removeAttribute('data-open');
      }
    }, { passive: true });
  }

  /* ── 15. RESPONSIVE: RE-ORIENT TRENDING TOPICS ───────── */
  function initTrendingTopicsResponsive() {
    const stage = qs('[data-tt]');
    if (!stage) return;

    function checkOrientation() {
      const isMobile = window.innerWidth <= 1100;
      stage.classList.toggle('is-mobile-layout', isMobile);
    }

    checkOrientation();
    on(window, 'resize', checkOrientation, { passive: true });
  }

  /* ── 16. RESPONSIVE MARKET BELT ─────────────────────── */
  function initMarketBeltScroll() {
    const belt = qs('[data-market-belt]');
    if (!belt) return;

    const container = belt.parentElement;
    if (!container) return;

    // Enable horizontal scroll on small screens
    if (window.innerWidth < 768) {
      container.style.overflowX = 'auto';
      container.style.webkitOverflowScrolling = 'touch';
    }
  }

  /* ── 17. FORM: PREVENT ZOOM ON FOCUS (iOS) ───────────── */
  function preventIOSZoom() {
    // iOS zooms in when input font-size < 16px; patch existing inputs
    if (!/iPhone|iPad|iPod/.test(navigator.userAgent)) return;

    const inputs = qsa('input[type="text"], input[type="email"], input[type="password"], input[type="search"], textarea, select');
    inputs.forEach(el => {
      const cs = window.getComputedStyle(el);
      const size = parseFloat(cs.fontSize);
      if (size < 16) {
        el.style.fontSize = '16px';
      }
    });
  }

  /* ── 18. RESPONSIVE: MODAL SHEET ON MOBILE ───────────── */
  function initModalSheets() {
    // Watch for Filament modals and add bottom-sheet behavior on mobile
    const observer = new MutationObserver(mutations => {
      mutations.forEach(m => {
        m.addedNodes.forEach(node => {
          if (node.nodeType !== 1) return;
          const modal = node.matches?.('.fi-modal-window') ? node : node.querySelector?.('.fi-modal-window');
          if (modal && window.innerWidth <= 640) {
            modal.style.cssText += `
              position: fixed !important;
              bottom: 0 !important;
              left: 0 !important;
              right: 0 !important;
              top: auto !important;
              max-width: 100% !important;
              width: 100% !important;
              border-radius: 12px 12px 0 0 !important;
              max-height: 90svh !important;
              overflow-y: auto !important;
            `;
          }
        });
      });
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }

  /* ── 19. SMOOTH SCROLL FOR ANCHOR LINKS ─────────────── */
  function initSmoothAnchors() {
    on(document, 'click', e => {
      const anchor = e.target.closest('a[href^="#"]');
      if (!anchor) return;
      const id = anchor.getAttribute('href').slice(1);
      const target = qs(`#${id}`);
      if (!target) return;
      e.preventDefault();
      const headerH = (qs('#siteHeader')?.offsetHeight || 0) + 16;
      const y = target.getBoundingClientRect().top + window.scrollY - headerH;
      window.scrollTo({ top: y, behavior: 'smooth' });
      target.focus({ preventScroll: true });
    });
  }

  /* ── 20. INERT BACKGROUND WHEN OVERLAYS OPEN ─────────── */
  function initInertBackgrounds() {
    function setInert(selector, isOpen) {
      const main = qs('main.site-main');
      const footer = qs('footer');
      [main, footer].forEach(el => {
        if (!el) return;
        if (isOpen) {
          el.setAttribute('inert', '');
          el.setAttribute('aria-hidden', 'true');
        } else {
          el.removeAttribute('inert');
          el.removeAttribute('aria-hidden');
        }
      });
    }

    // Cookie modal
    const ccModal = qs('#gtCookieModal');
    if (ccModal) {
      const observer = new MutationObserver(() => {
        setInert('#gtCookieModal', ccModal.classList.contains('gt-cc-visible'));
      });
      observer.observe(ccModal, { attributes: true, attributeFilter: ['class'] });
    }

    // Promo overlay
    const promoOverlay = qs('#gtPromoOverlay');
    if (promoOverlay) {
      const observer = new MutationObserver(() => {
        setInert('#gtPromoOverlay', promoOverlay.classList.contains('is-open'));
      });
      observer.observe(promoOverlay, { attributes: true, attributeFilter: ['class'] });
    }
  }

  /* ── INIT ─────────────────────────────────────────────── */
  function init() {
    injectSkipLink();
    initMobileMenu();
    initSmartHeader();
    initSwipeCarousels();
    initLazyImages();
    initResponsiveTables();
    initFilterToggle();
    initScrollTop();
    markActiveNavLink();
    preventDoubleTapZoom();
    initHeroOffset();
    initLangMenuA11y();
    initTrendingTopicsResponsive();
    initMarketBeltScroll();
    preventIOSZoom();
    initModalSheets();
    initSmoothAnchors();
    initInertBackgrounds();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();