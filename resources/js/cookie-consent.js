/**
 * GT Cookie Consent Manager
 * Enterprise-grade GDPR-compliant consent management
 *
 * Storage key: gt_cc_v{version} (bumped by consent_version setting)
 * Consent object: { v: "1.0", ts: 1234567890, consents: { necessary: true, analytics: false, ... } }
 */

const CC = (() => {
  // ── Constants ──────────────────────────────────────────────────────
  const STORAGE_PREFIX = 'gt_cc_';
  const EVENT_CHANGED  = 'gt:consent:changed';
  const EVENT_READY    = 'gt:consent:ready';

  // ── State ──────────────────────────────────────────────────────────
  let payload    = null;  // from <script id="gt-cookie-payload">
  let consents   = {};    // current resolved consent state
  let storageKey = STORAGE_PREFIX + 'v1';

  // ── DOM refs ───────────────────────────────────────────────────────
  let banner, modal, modalPanel, categoriesEl;

  // ── Helpers ────────────────────────────────────────────────────────
  const readStorage = () => {
    try {
      const raw = localStorage.getItem(storageKey);
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== 'object') return null;
      return parsed;
    } catch { return null; }
  };

  const writeStorage = (data) => {
    try {
      localStorage.setItem(storageKey, JSON.stringify(data));
    } catch { /* storage full or private mode */ }
  };

  const dispatch = (eventName, detail) => {
    window.dispatchEvent(new CustomEvent(eventName, { detail }));
  };

  // ── Script loader: deferred loading for consented scripts ──────────
  const loadScript = (src, id) => {
    if (document.getElementById(id)) return;
    const s = document.createElement('script');
    s.async = true;
    s.src   = src;
    s.id    = id;
    document.head.appendChild(s);
  };

  // ── Apply consent: trigger side effects ───────────────────────────
  const applyConsent = (c) => {
    consents = c;

    // Analytics (GA4)
    const gaId = document.documentElement.dataset.gaId;
    if (c.analytics && gaId) {
      loadScript(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(gaId)}`, 'gt-ga4-script');
      if (!window.dataLayer) {
        window.dataLayer = [];
        window.gtag = function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', gaId);
      }
    }

    // Social consent: ungate trending topics cards
    document.querySelectorAll('[data-social-card]').forEach(card => {
      const src = (card.getAttribute('data-source') || '').toLowerCase();
      if (src === 'instagram') {
        card.classList.remove('needs-consent');
      } else {
        card.classList.toggle('needs-consent', !c.social);
      }
    });

    // Marketing: future integrations (HotJar, etc.) go here
    // if (c.marketing) { ... }

    dispatch(EVENT_CHANGED, { consents: c });
  };

  // ── Persist + server-log ──────────────────────────────────────────
  const persistConsent = (c) => {
    const stored = {
      v:        payload?.version ?? '1.0',
      ts:       Math.floor(Date.now() / 1000),
      consents: c,
    };
    writeStorage(stored);

    // Server-side GDPR audit log (fire and forget)
    const locale = document.documentElement.lang || 'en';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch(`/${locale}/cookie-consent`, {
      method:  'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept':       'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({ consents: c }),
      keepalive: true,
    }).catch(() => { /* non-critical */ });
  };

  // ── Banner visibility ─────────────────────────────────────────────
  const showBanner = () => {
    if (!banner) return;
    banner.setAttribute('data-initialized', '');
    banner.classList.remove('hidden');
    banner.removeAttribute('aria-hidden');
  };

  const hideBanner = () => {
    if (!banner) return;
    banner.classList.add('hidden');
    banner.setAttribute('aria-hidden', 'true');
  };

  // ── Modal ─────────────────────────────────────────────────────────
  const buildCategoryHTML = (cat, checked) => {
    const isRequired = cat.required;
    const toggleId   = `gt-toggle-${cat.key}`;

    return `
      <div class="gt-cookie-category" data-category="${cat.key}">
        <div class="gt-cookie-category__header" role="button" tabindex="0"
             aria-expanded="false" data-cat-toggle>
          <div class="gt-cookie-category__info">
            <div class="gt-cookie-category__name">
              ${escHtml(cat.label)}
              ${isRequired ? `<span class="gt-cookie-category__badge">${escHtml(window.__cookieAlwaysActive || 'Always active')}</span>` : ''}
            </div>
            <div class="gt-cookie-category__desc-toggle">${escHtml(cat.description)}</div>
          </div>
          ${isRequired
            ? `<label class="gt-cookie-toggle">
                 <input type="checkbox" id="${toggleId}" checked disabled aria-label="${escHtml(cat.label)}">
                 <span class="gt-cookie-toggle__track" aria-hidden="true"></span>
               </label>`
            : `<label class="gt-cookie-toggle" onclick="event.stopPropagation()">
                 <input type="checkbox" id="${toggleId}" name="${cat.key}"
                        ${checked ? 'checked' : ''}
                        aria-label="${escHtml(cat.label)}">
                 <span class="gt-cookie-toggle__track" aria-hidden="true"></span>
               </label>`}
        </div>
        <div class="gt-cookie-category__body">${escHtml(cat.description)}</div>
      </div>`;
  };

  const renderCategories = (currentConsents) => {
    if (!categoriesEl || !payload) return;

    categoriesEl.innerHTML = (payload.categories || [])
      .map(cat => buildCategoryHTML(cat, currentConsents[cat.key] !== false))
      .join('');

    // Accordion expand/collapse
    categoriesEl.querySelectorAll('[data-cat-toggle]').forEach(header => {
      const row = header.closest('.gt-cookie-category');
      const expand = () => {
        const open = row.classList.toggle('is-open');
        header.setAttribute('aria-expanded', open ? 'true' : 'false');
      };
      header.addEventListener('click', expand);
      header.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); expand(); }
      });
    });
  };

  const showModal = () => {
    if (!modal) return;
    renderCategories(consents);
    modal.classList.remove('hidden');
    modal.removeAttribute('aria-hidden');
    hideBanner();
    trapFocus(modalPanel);
    modalPanel?.focus();
  };

  const hideModal = () => {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    releaseFocus();
    showBanner();
  };

  // ── Collect toggles from modal ────────────────────────────────────
  const collectModalConsents = () => {
    const c = { necessary: true };
    if (!categoriesEl) return c;
    categoriesEl.querySelectorAll('input[name]').forEach(input => {
      c[input.name] = input.checked;
    });
    return c;
  };

  // ── Accept / Reject helpers ───────────────────────────────────────
  const buildConsentsAll = (value) => {
    const c = { necessary: true };
    (payload?.categories || []).forEach(cat => {
      c[cat.key] = cat.required ? true : value;
    });
    return c;
  };

  const acceptAll = () => {
    const c = buildConsentsAll(true);
    persistConsent(c);
    applyConsent(c);
    hideBanner();
    hideModal();
  };

  const rejectAll = () => {
    const c = buildConsentsAll(false);
    persistConsent(c);
    applyConsent(c);
    hideBanner();
  };

  const savePreferences = () => {
    const c = collectModalConsents();
    persistConsent(c);
    applyConsent(c);
    hideModal();
    hideBanner();
  };

  // ── Focus trap ────────────────────────────────────────────────────
  let _focusTrapEl = null;
  let _prevFocused = null;

  const trapFocus = (el) => {
    _prevFocused = document.activeElement;
    _focusTrapEl = el;
    document.addEventListener('keydown', onFocusTrapKeydown);
  };

  const releaseFocus = () => {
    document.removeEventListener('keydown', onFocusTrapKeydown);
    _focusTrapEl = null;
    _prevFocused?.focus?.();
  };

  const onFocusTrapKeydown = (e) => {
    if (!_focusTrapEl) return;
    if (e.key !== 'Tab') return;

    const focusable = Array.from(_focusTrapEl.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )).filter(el => !el.disabled && el.offsetParent !== null);

    if (!focusable.length) { e.preventDefault(); return; }

    const first = focusable[0];
    const last  = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault(); last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault(); first.focus();
    }
  };

  // ── Escape handler ─────────────────────────────────────────────────
  const onEscapeKey = (e) => {
    if (e.key === 'Escape') {
      if (!modal?.classList.contains('hidden')) hideModal();
    }
  };

  // ── Sanitize helper ───────────────────────────────────────────────
  const escHtml = (str) =>
    String(str ?? '').replace(/[&<>"']/g, m =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])
    );

  // ── Init ─────────────────────────────────────────────────────────
  const init = () => {
    // Read server-rendered payload
    const payloadEl = document.getElementById('gt-cookie-payload');
    if (!payloadEl) return;

    try {
      payload = JSON.parse(payloadEl.textContent || '{}');
    } catch { return; }

    // Set storage key based on version
    storageKey = STORAGE_PREFIX + 'v' + (payload.version || '1').replace(/\./g, '_');

    // DOM refs
    banner      = document.getElementById('gtCookieBanner');
    modal       = document.getElementById('gtCookieModal');
    modalPanel  = document.getElementById('gtCookieModal')?.querySelector('.gt-cookie-modal__panel');
    categoriesEl = document.getElementById('gtCookieCategories');

    // Wire buttons
    document.getElementById('gtCookieAcceptBtn')?.addEventListener('click', acceptAll);
    document.getElementById('gtCookieAcceptAllModalBtn')?.addEventListener('click', acceptAll);
    document.getElementById('gtCookieRejectBtn')?.addEventListener('click', rejectAll);
    document.getElementById('gtCookieManageBtn')?.addEventListener('click', showModal);
    document.getElementById('gtCookieSaveBtn')?.addEventListener('click', savePreferences);
    document.getElementById('gtCookieModalClose')?.addEventListener('click', hideModal);
    document.getElementById('gtCookieModalBackdrop')?.addEventListener('click', hideModal);

    document.addEventListener('keydown', onEscapeKey);

    // Check existing consent
    const stored = readStorage();
    const versionMatches = stored?.v === payload.version;

    if (stored && stored.consents && versionMatches) {
      // Previously consented — silently apply
      consents = stored.consents;
      applyConsent(consents);
    } else {
      // No consent or version bumped — show banner
      // Build defaults (necessary = true, others = null/not set)
      consents = { necessary: true };
      showBanner();
    }

    dispatch(EVENT_READY, { consents });
  };

  // ── Public API (window.GtCookieConsent) ───────────────────────────
  return {
    init,
    acceptAll,
    rejectAll,
    showModal,
    hideModal,
    hasConsent: (category) => consents[category] === true,
    getConsents: () => ({ ...consents }),
    on: (event, handler) => window.addEventListener(event, handler),
    off: (event, handler) => window.removeEventListener(event, handler),
    EVENT_CHANGED,
    EVENT_READY,
  };
})();

// Expose globally for external integrations
window.GtCookieConsent = CC;

// Auto-init on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => CC.init());
} else {
  CC.init();
}

export default CC;