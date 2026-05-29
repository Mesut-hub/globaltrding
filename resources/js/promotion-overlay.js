// resources/js/promotion-overlay.js
/**
 * GT Promotion Overlay System v1.0
 * ─────────────────────────────────────────────────────────────────────────
 * Enterprise-grade advertisement/promotion display engine.
 *
 * Architecture:
 *  - Payload loaded from <script type="application/json" id="gt-promo-payload">
 *  - Display frequency tracked in localStorage / sessionStorage (per promo ID)
 *  - Page matching uses simple glob patterns (path starts-with / exact)
 *  - Focus trap follows WCAG 2.1 success criterion 2.1.2
 *  - Escape key closes overlay
 *  - RTL-aware
 */

const GtPromo = (() => {
  // ── Constants ───────────────────────────────────────────────────────────
  const STORAGE_PREFIX = 'gt_promo_seen_';
  const CLASS_OPEN     = 'is-open';

  // ── State ────────────────────────────────────────────────────────────────
  let promotions    = [];
  let currentPromo  = null;
  let autoTimer     = null;

  // ── DOM refs ──────────────────────────────────────────────────────────────
  let $overlay, $panel, $content, $closeBtn, $backdrop, $trigger;

  // ── HTML escaping ─────────────────────────────────────────────────────────
  const esc = (s) =>
    String(s ?? '').replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));

  // ── Frequency / storage helpers ───────────────────────────────────────────

  /**
   * Returns true when the visitor is allowed to see this promotion again
   * according to its configured display_frequency.
   */
  const canShow = (promo) => {
    const key  = STORAGE_PREFIX + promo.id;
    const freq = promo.display_frequency;

    if (freq === 'always') return true;

    if (freq === 'once_per_session') {
      try { return !sessionStorage.getItem(key); } catch { return true; }
    }

    if (freq === 'once_per_day') {
      try {
        const ts = localStorage.getItem(key);
        return !ts || (Date.now() - parseInt(ts, 10)) > 86_400_000;
      } catch { return true; }
    }

    if (freq === 'once_per_week') {
      try {
        const ts = localStorage.getItem(key);
        return !ts || (Date.now() - parseInt(ts, 10)) > 604_800_000;
      } catch { return true; }
    }

    if (freq === 'once_ever') {
      try { return !localStorage.getItem(key); } catch { return true; }
    }

    return true;
  };

  const recordShown = (promo) => {
    const key  = STORAGE_PREFIX + promo.id;
    const freq = promo.display_frequency;

    if (freq === 'always') return;

    try {
      if (freq === 'once_per_session') {
        sessionStorage.setItem(key, '1');
      } else if (['once_per_day', 'once_per_week', 'once_ever'].includes(freq)) {
        localStorage.setItem(key, String(Date.now()));
      }
    } catch { /* storage blocked — ignore */ }
  };

  // ── Page targeting ────────────────────────────────────────────────────────

  const matchesCurrentPage = (promo) => {
    const pages = promo.target_pages;
    if (!pages || pages.length === 0 || pages.includes('*')) return true;

    const path = window.location.pathname;

    return pages.some((pattern) => {
      if (!pattern || pattern === '*') return true;
      // Glob prefix match: "/en/products*" matches "/en/products", "/en/products/foo"
      if (pattern.endsWith('*')) {
        return path.startsWith(pattern.slice(0, -1));
      }
      // Exact match
      return path === pattern;
    });
  };

  // ── Content builder ───────────────────────────────────────────────────────

  const buildHTML = (promo) => {
    let html = '';

    // Media block
    if (promo.media_type === 'image' && promo.media_url) {
      html += `<div class="gt-promo-media">
        <img
          src="${esc(promo.media_url)}"
          alt="${esc(promo.title)}"
          loading="lazy"
          decoding="async"
        >
      </div>`;
    } else if (promo.media_type === 'video' && promo.media_url) {
      const poster = promo.thumbnail_url ? ` poster="${esc(promo.thumbnail_url)}"` : '';
      html += `<div class="gt-promo-media">
        <video autoplay muted loop playsinline${poster}>
          <source src="${esc(promo.media_url)}" type="video/mp4">
        </video>
      </div>`;
    }

    // Text body
    const bodyStyle = `background:${esc(promo.bg_color)};color:${esc(promo.text_color)};`;

    html += `<div class="gt-promo-body" style="${bodyStyle}">`;

    if (promo.title) {
      html += `<h2 class="gt-promo-title" id="gtPromoTitle">${esc(promo.title)}</h2>`;
    }

    if (promo.content) {
      html += `<p class="gt-promo-text">${esc(promo.content)}</p>`;
    }

    if (promo.cta_label && promo.cta_url) {
      const ctaStyle = `background:${esc(promo.cta_bg_color)};color:${esc(promo.cta_text_color)};`;
      const ctaUrl   = promo.cta_url.replace('{locale}', document.documentElement.lang || 'en');
      const rel      = promo.cta_target === '_blank' ? ' rel="noopener noreferrer"' : '';

      html += `<a
        class="gt-promo-cta"
        href="${esc(ctaUrl)}"
        target="${esc(promo.cta_target)}"
        style="${ctaStyle}"${rel}
      >${esc(promo.cta_label)}</a>`;
    }

    html += `</div>`;

    return html;
  };

  // ── Open / close ──────────────────────────────────────────────────────────

  const open = (promo) => {
    if (!$overlay || !promo) return;

    currentPromo = promo;

    // ── Set animation
    $overlay.setAttribute('data-animation', promo.animation_type || 'slide_up');

    // ── Set position class
    const posMap = {
      bottom:         'gt-promo-overlay--bottom',
      'bottom-left':  'gt-promo-overlay--bottom-left',
      'bottom-right': 'gt-promo-overlay--bottom-right',
    };
    $overlay.className = 'gt-promo-overlay';
    if (posMap[promo.overlay_position]) {
      $overlay.classList.add(posMap[promo.overlay_position]);
    }

    // ── Set panel size
    $panel.className = `gt-promo-panel gt-promo-panel--${promo.overlay_size || 'md'}`;

    // ── Configure close affordances
    $closeBtn.hidden  = !promo.show_close_button;
    $backdrop.style.pointerEvents = promo.close_on_backdrop ? 'auto' : 'none';

    // ── Inject content
    $content.innerHTML = buildHTML(promo);

    // ── Open (next frame ensures transitions fire)
    requestAnimationFrame(() => {
      $overlay.classList.add(CLASS_OPEN);
      $overlay.setAttribute('aria-hidden', 'false');
    });

    // ── Lock body scroll
    document.documentElement.classList.add('overflow-hidden');
    document.body.classList.add('overflow-hidden');

    // ── Focus panel + trap
    requestAnimationFrame(() => {
      $panel.focus();
      trapFocus($panel);
    });

    // ── Record for frequency tracking
    recordShown(promo);
  };

  const close = () => {
    if (!$overlay || !$overlay.classList.contains(CLASS_OPEN)) return;

    $overlay.classList.remove(CLASS_OPEN);
    $overlay.setAttribute('aria-hidden', 'true');

    document.documentElement.classList.remove('overflow-hidden');
    document.body.classList.remove('overflow-hidden');

    releaseFocus();
    currentPromo = null;
  };

  // ── Focus trap (WCAG 2.1 SC 2.1.2) ──────────────────────────────────────

  let _trapEl    = null;
  let _prevFocus = null;

  const trapFocus = (el) => {
    _prevFocus = document.activeElement instanceof HTMLElement
      ? document.activeElement
      : null;
    _trapEl = el;
    document.addEventListener('keydown', onTrapKeydown);
  };

  const releaseFocus = () => {
    document.removeEventListener('keydown', onTrapKeydown);
    _trapEl = null;
    _prevFocus?.focus?.();
    _prevFocus = null;
  };

  const onTrapKeydown = (e) => {
    if (!_trapEl || e.key !== 'Tab') return;

    const focusable = Array.from(
      _trapEl.querySelectorAll(
        'button:not([disabled]):not([hidden]), [href], input:not([disabled]),' +
        ' select:not([disabled]), textarea:not([disabled]),' +
        ' [tabindex]:not([tabindex="-1"])'
      )
    ).filter((el) => el.offsetParent !== null);

    if (!focusable.length) { e.preventDefault(); return; }

    const first = focusable[0];
    const last  = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  };

  // ── Page selector ─────────────────────────────────────────────────────────

  /**
   * Find the best matching promotion for the current page and display mode.
   * Respects priority order (set by admin, higher = first).
   */
  const findBest = (mode) =>
    promotions.find(
      (p) => p.display_mode === mode && matchesCurrentPage(p) && canShow(p)
    ) ?? null;

  // ── Init ──────────────────────────────────────────────────────────────────

  const init = () => {
    const payloadEl = document.getElementById('gt-promo-payload');

    try {
      promotions = payloadEl ? JSON.parse(payloadEl.textContent || '[]') : [];
    } catch (err) {
      console.warn('[GtPromo] Failed to parse payload:', err);
      promotions = [];
    }

    // ── Always resolve DOM refs and wire events ───────────────────────────
    $overlay  = document.getElementById('gtPromoOverlay');
    $panel    = document.getElementById('gtPromoPanel');
    $content  = document.getElementById('gtPromoContent');
    $closeBtn = document.getElementById('gtPromoClose');
    $backdrop = document.getElementById('gtPromoBackdrop');
    $trigger  = document.getElementById('gtPromoTrigger');

    $closeBtn?.addEventListener('click', close);
    $backdrop?.addEventListener('click', () => {
      if (currentPromo?.close_on_backdrop) close();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && $overlay?.classList.contains(CLASS_OPEN)) close();
    });

    // ── Wire trigger button (always visible, badge only if promo exists) ──
    if ($trigger) {
      const manualPromoForPage = promotions.find(
        (p) => p.display_mode === 'manual' && matchesCurrentPage(p)
      );

      if (manualPromoForPage) {
        const badge = $trigger.querySelector('.gt-promo-trigger__badge');
        if (badge) badge.hidden = false;
      }

      $trigger.addEventListener('click', () => {
        if ($overlay?.classList.contains(CLASS_OPEN)) {
          close();
          return;
        }
        const promo = findBest('manual')
          ?? promotions.find((p) => p.display_mode === 'manual' && matchesCurrentPage(p))
          ?? null;
        if (promo) open(promo);
      });
    }

    // ── Nothing else to do if no promotions are active ────────────────────
    if (!promotions.length) return;

    // ── Schedule auto-show ────────────────────────────────────────────────
    const autoPromo = findBest('auto');
    if (autoPromo) {
      const delay = Math.max(500, autoPromo.auto_show_delay_ms ?? 2500);
      autoTimer = setTimeout(() => {
        if (!$overlay?.classList.contains(CLASS_OPEN)) {
          open(autoPromo);
        }
      }, delay);
    }
  };

  // ── Auto-init ─────────────────────────────────────────────────────────────

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // ── Public API ────────────────────────────────────────────────────────────
  return { init, open, close };
})();

window.GtPromo = GtPromo;
export default GtPromo;