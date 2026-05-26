/**
 * GT Cookie Consent Manager v2
 * Enterprise GDPR-compliant consent management
 *
 * Key fix: uses .gt-cc-visible / .gt-cc-hidden CSS classes
 * instead of Tailwind's .hidden to avoid display:none !important conflicts.
 */

const CC = (() => {
  // ── Constants ──────────────────────────────────────────────────
  const STORAGE_PREFIX  = 'gt_cc_';
  const EVENT_CHANGED   = 'gt:consent:changed';
  const EVENT_READY     = 'gt:consent:ready';
  const CLASS_VISIBLE   = 'gt-cc-visible';   // shows element
  const CLASS_HIDDEN    = 'gt-cc-hidden';    // hides element

  // ── State ──────────────────────────────────────────────────────
  let payload    = null;
  let consents   = {};
  let storageKey = `${STORAGE_PREFIX}v1`;

  // ── DOM refs (populated in init) ───────────────────────────────
  let banner, modal, modalPanel, categoriesEl;

  // ── Helpers ────────────────────────────────────────────────────
  const show = (el) => {
    if (!el) return;
    el.classList.remove(CLASS_HIDDEN);
    el.classList.add(CLASS_VISIBLE);
    el.removeAttribute('aria-hidden');
  };

  const hide = (el) => {
    if (!el) return;
    el.classList.remove(CLASS_VISIBLE);
    el.classList.add(CLASS_HIDDEN);
    el.setAttribute('aria-hidden', 'true');
  };

  const isVisible = (el) => el && el.classList.contains(CLASS_VISIBLE);

  const readStorage = () => {
    try {
      const raw = localStorage.getItem(storageKey);
      if (!raw) return null;
      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' ? parsed : null;
    } catch { return null; }
  };

  const writeStorage = (data) => {
    try { localStorage.setItem(storageKey, JSON.stringify(data)); }
    catch { /* quota exceeded / private mode */ }
  };

  const dispatch = (name, detail) =>
    window.dispatchEvent(new CustomEvent(name, { detail }));

  const esc = (s) => String(s ?? '').replace(
    /[&<>"']/g,
    (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
  );

  // ── Script loader for consented third-party scripts ────────────
  const loadScript = (src, id) => {
    if (document.getElementById(id)) return;
    const s = document.createElement('script');
    s.async = true;
    s.src   = src;
    s.id    = id;
    document.head.appendChild(s);
  };

  // ── Apply consent side-effects ─────────────────────────────────
  const applyConsent = (c) => {
    consents = c;

    // Google Analytics 4
    const gaId = document.documentElement.dataset.gaId;
    if (c.analytics && gaId) {
      loadScript(
        `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(gaId)}`,
        'gt-ga4-script'
      );
      if (!window.gtag) {
        window.dataLayer = window.dataLayer || [];
        window.gtag = (...args) => window.dataLayer.push(args);
        window.gtag('js', new Date());
        window.gtag('config', gaId, { anonymize_ip: true });
      }
    }

    // Social media consent gate (Trending Topics cards)
    document.querySelectorAll('[data-social-card]').forEach((card) => {
      const src = (card.getAttribute('data-source') || '').toLowerCase();
      if (src === 'instagram') {
        card.classList.remove('needs-consent');
      } else {
        card.classList.toggle('needs-consent', !c.social);
      }
    });

    dispatch(EVENT_CHANGED, { consents: c });
  };

  // ── Persist consent and log to server ─────────────────────────
  const persistConsent = (c) => {
    const stored = {
      v:        payload?.version ?? '1.0',
      ts:       Math.floor(Date.now() / 1000),
      consents: c,
    };
    writeStorage(stored);

    // Fire-and-forget GDPR audit log
    const locale     = document.documentElement.lang || 'en';
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fetch(`/${locale}/cookie-consent`, {
      method:   'POST',
      headers:  {
        'Content-Type': 'application/json',
        'Accept':       'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body:      JSON.stringify({ consents: c }),
      keepalive: true,
    }).catch(() => {});
  };

  // ── Build all consents to a single value (true/false) ──────────
  const buildConsentsAll = (value) => {
    const c = { necessary: true };
    (payload?.categories ?? []).forEach((cat) => {
      c[cat.key] = cat.required ? true : value;
    });
    return c;
  };

  // ── Collect toggle values from the modal form ──────────────────
  const collectModalConsents = () => {
    const c = { necessary: true };
    categoriesEl?.querySelectorAll('input[name]').forEach((input) => {
      c[input.name] = input.checked;
    });
    return c;
  };

  // ── Action handlers ────────────────────────────────────────────
  const acceptAll = () => {
    const c = buildConsentsAll(true);
    persistConsent(c);
    applyConsent(c);
    hide(banner);
    hide(modal);
  };

  const rejectAll = () => {
    const c = buildConsentsAll(false);
    persistConsent(c);
    applyConsent(c);
    hide(banner);
  };

  const savePreferences = () => {
    const c = collectModalConsents();
    persistConsent(c);
    applyConsent(c);
    hide(modal);
    hide(banner);
    releaseFocus();
  };

  // ── Modal render ───────────────────────────────────────────────
  const buildCategoryHTML = (cat, checked) => {
    const alwaysActive = window.__cookieAlwaysActive || 'Always active';

    return `
      <div class="gt-cookie-category" data-category="${esc(cat.key)}">
        <div class="gt-cookie-category__header"
             role="button"
             tabindex="0"
             aria-expanded="false"
             data-cat-toggle>
          <div class="gt-cookie-category__info">
            <div class="gt-cookie-category__name">
              ${esc(cat.label)}
              ${cat.required
                ? `<span class="gt-cookie-category__badge">${esc(alwaysActive)}</span>`
                : ''}
            </div>
            <div class="gt-cookie-category__preview">${esc(cat.description)}</div>
          </div>

          ${cat.required
            ? `<label class="gt-cookie-toggle">
                 <input type="checkbox" id="gc-${esc(cat.key)}" checked disabled
                        aria-label="${esc(cat.label)}">
                 <span class="gt-cookie-toggle__track" aria-hidden="true"></span>
               </label>`
            : `<label class="gt-cookie-toggle" onclick="event.stopPropagation()">
                 <input type="checkbox" id="gc-${esc(cat.key)}" name="${esc(cat.key)}"
                        ${checked ? 'checked' : ''}
                        aria-label="${esc(cat.label)}">
                 <span class="gt-cookie-toggle__track" aria-hidden="true"></span>
               </label>`
          }
        </div>
        <div class="gt-cookie-category__body"
             id="gc-desc-${esc(cat.key)}"
             role="region"
             aria-labelledby="gc-header-${esc(cat.key)}">
          ${esc(cat.description)}
        </div>
      </div>`;
  };

  const renderCategories = (currentConsents) => {
    if (!categoriesEl || !payload) return;

    categoriesEl.innerHTML = (payload.categories ?? [])
      .map((cat) => buildCategoryHTML(cat, currentConsents[cat.key] !== false))
      .join('');

    // Accordion interaction
    categoriesEl.querySelectorAll('[data-cat-toggle]').forEach((header) => {
      const row = header.closest('.gt-cookie-category');
      const toggle = () => {
        const open = row.classList.toggle('is-open');
        header.setAttribute('aria-expanded', String(open));
      };
      header.addEventListener('click', toggle);
      header.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
      });
    });
  };

  // ── Modal show/hide ────────────────────────────────────────────
  const showModal = () => {
    if (!modal) return;
    renderCategories(consents);
    show(modal);
    hide(banner);
    trapFocus(modalPanel);
    modalPanel?.focus();
  };

  const hideModal = () => {
    if (!modal) return;
    hide(modal);
    releaseFocus();
    // Re-show banner only if no consent recorded yet
    const stored = readStorage();
    if (!stored?.consents) show(banner);
  };

  // ── Focus trap ─────────────────────────────────────────────────
  let _trapEl  = null;
  let _prevFocus = null;

  const trapFocus = (el) => {
    _prevFocus = document.activeElement;
    _trapEl    = el;
    document.addEventListener('keydown', onTrapKeydown);
  };

  const releaseFocus = () => {
    document.removeEventListener('keydown', onTrapKeydown);
    _trapEl = null;
    _prevFocus?.focus?.();
  };

  const onTrapKeydown = (e) => {
    if (!_trapEl || e.key !== 'Tab') return;

    const focusable = Array.from(
      _trapEl.querySelectorAll(
        'button:not([disabled]), [href], input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])'
      )
    ).filter((el) => el.offsetParent !== null);

    if (!focusable.length) { e.preventDefault(); return; }

    const first = focusable[0];
    const last  = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault(); last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault(); first.focus();
    }
  };

  // ── Keyboard escape ────────────────────────────────────────────
  const onEscape = (e) => {
    if (e.key === 'Escape' && isVisible(modal)) hideModal();
  };

  // ── Init ───────────────────────────────────────────────────────
  const init = () => {
    const payloadEl = document.getElementById('gt-cookie-payload');
    if (!payloadEl) return; // component not on this page

    try {
      payload = JSON.parse(payloadEl.textContent || '{}');
    } catch (err) {
      console.warn('[GtCookieConsent] Failed to parse payload:', err);
      return;
    }

    // Derive storage key from consent version (bump to force re-consent)
    storageKey = `${STORAGE_PREFIX}v${(payload.version || '1').replace(/\./g, '_')}`;

    // DOM refs
    banner       = document.getElementById('gtCookieBanner');
    modal        = document.getElementById('gtCookieModal');
    modalPanel   = document.getElementById('gtCookieModalPanel');
    categoriesEl = document.getElementById('gtCookieCategories');

    // Wire up buttons
    document.getElementById('gtCookieAcceptBtn')?.addEventListener('click', acceptAll);
    document.getElementById('gtCookieAcceptAllModalBtn')?.addEventListener('click', acceptAll);
    document.getElementById('gtCookieRejectBtn')?.addEventListener('click', rejectAll);
    document.getElementById('gtCookieManageBtn')?.addEventListener('click', showModal);
    document.getElementById('gtCookieSaveBtn')?.addEventListener('click', savePreferences);
    document.getElementById('gtCookieModalClose')?.addEventListener('click', hideModal);
    document.getElementById('gtCookieModalBackdrop')?.addEventListener('click', hideModal);

    document.addEventListener('keydown', onEscape);

    // Evaluate existing consent
    const stored        = readStorage();
    const versionMatch  = stored?.v === payload.version;

    if (stored?.consents && versionMatch) {
      // Silently re-apply prior consent
      applyConsent(stored.consents);
    } else {
      // No valid consent recorded → show banner
      consents = { necessary: true };
      show(banner);
    }

    dispatch(EVENT_READY, { consents });
  };

  // ── Public API ─────────────────────────────────────────────────
  return {
    init,
    acceptAll,
    rejectAll,
    showModal,
    hideModal,
    hasConsent:  (key) => consents[key] === true,
    getConsents: ()    => ({ ...consents }),
    on:          (ev, fn) => window.addEventListener(ev, fn),
    off:         (ev, fn) => window.removeEventListener(ev, fn),
    EVENT_CHANGED,
    EVENT_READY,
  };
})();

window.GtCookieConsent = CC;

// Auto-init
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => CC.init());
} else {
  CC.init();
}

export default CC;