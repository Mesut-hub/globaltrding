// resources/js/home.js

document.addEventListener('DOMContentLoaded', () => {

  // ─── Cookie consent shim ──────────────────────────────────────────────────
  // Provides window.__cookieConsent if your app hasn't defined it yet.
  // Replace with your real cookie API. Persists in localStorage.
  if (!window.__cookieConsent) {
    const _KEY = 'gt_cookie_consent';
    window.__cookieConsent = {
      read() {
        try { return JSON.parse(localStorage.getItem(_KEY) || 'null') || {}; }
        catch { return {}; }
      },
      write(prefs) {
        try {
          const next = { ...this.read(), ...prefs };
          localStorage.setItem(_KEY, JSON.stringify(next));
          window.dispatchEvent(new CustomEvent('cookie-consent:changed', { detail: next }));
        } catch {}
      },
    };
  }

  // ─── Industries slider ────────────────────────────────────────────────────
  document.querySelectorAll('[data-industry-slider]').forEach((root) => {
    const track = root.querySelector('[data-ind="track"]');
    const prev  = root.querySelector('[data-ind="prev"]');
    const next  = root.querySelector('[data-ind="next"]');
    if (!track || !prev || !next) return;

    const step = () => Math.min(track.clientWidth * 0.9, 520);
    const updateNavState = () => {
      const eps = 2;
      const atStart = track.scrollLeft <= eps;
      const atEnd   = track.scrollLeft + track.clientWidth >= track.scrollWidth - eps;
      prev.disabled = atStart; next.disabled = atEnd;
      prev.classList.toggle('is-disabled', atStart);
      next.classList.toggle('is-disabled', atEnd);
      root.classList.toggle('at-start', atStart);
      root.classList.toggle('at-end',   atEnd);
    };
    prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next.addEventListener('click', () => track.scrollBy({ left:  step(), behavior: 'smooth' }));
    track.addEventListener('scroll', () => {
      if (track._raf) return;
      track._raf = requestAnimationFrame(() => { track._raf = null; updateNavState(); });
    }, { passive: true });
    updateNavState();
    window.addEventListener('resize', updateNavState, { passive: true });
  });

  // ─── Hero: video autoplay fallback ───────────────────────────────────────
  document.querySelectorAll('[data-hero]').forEach((section) => {
    const video  = section.querySelector('video');
    const poster = section.querySelector('[data-hero-poster]');
    if (!video || !poster) return;
    const p = video.play?.();
    if (p?.then) p.catch(() => { video.style.display = 'none'; poster.style.display = 'block'; });
  });


  // ═══════════════════════════════════════════════════════════════════════════
  //  TRENDING TOPICS
  // ═══════════════════════════════════════════════════════════════════════════

  function clamp(n, lo, hi) { return Math.max(lo, Math.min(hi, n)); }

  // ── SLOT_CONFIG ────────────────────────────────────────────────────────────
  // These numbers MUST exactly mirror the static translate3d values in home.css.
  // tx/ty/tz  = second translate3d x/y/z (px)
  // rx        = optional rotateX value (deg)  — 0 means omit
  // scale     = uniform scale factor
  // z, op     = z-index, opacity
  //
  // JS writes each card's full transform as an inline style string on load,
  // giving CSS transition a numeric "from" value to animate from on swap.
  const SLOT_CONFIG = {
    leftTop:    { tx: -530, ty: -220, tz: -240, rx: 0, scale: 0.672, z:  2, op: 0.82 },
    leftBottom: { tx: -580, ty:  195, tz: -280, rx: 0, scale: 0.672, z:  1, op: 0.78 },
    center:     { tx:    0, ty:   60, tz:    0, rx: 0, scale: 1,     z: 10, op: 1.00 },
    rightTop:   { tx:  395, ty: -210, tz: -180, rx: 4, scale: 0.672, z:  3, op: 0.82 },
    rightBottom:{ tx:  430, ty:  215, tz: -220, rx: 4, scale: 0.672, z:  2, op: 0.78 },
  };
  const ALL_SLOTS = Object.keys(SLOT_CONFIG);

  // Build the full CSS transform string for a given slot name.
  function buildTransform(slotName) {
    const c = SLOT_CONFIG[slotName];
    return (
      `translate3d(-50%,-50%,0px) ` +
      `translate3d(${c.tx}px,${c.ty}px,${c.tz}px) ` +
      (c.rx ? `rotateX(${c.rx}deg) ` : '') +
      `scale(${c.scale})`
    );
  }

  // Apply a slot's full visual state as inline styles.
  // Inline style beats class rule → CSS transition can interpolate between values.
  function applySlotStyle(el, slotName) {
    const c = SLOT_CONFIG[slotName];
    if (!c) return;
    ALL_SLOTS.forEach(s => el.classList.remove(`tt-slot--${s}`));
    el.classList.add(`tt-slot--${slotName}`);
    el.setAttribute('data-slot', slotName);
    el.style.transform = buildTransform(slotName);
    el.style.zIndex    = c.z;
    el.style.opacity   = c.op;
    // NOTE: cursor is NOT written here; CSS class handles it so hover still works.
  }

  // Seed inline transforms on all cards so the first swap has a "from" value.
  function initSlotStyles(stage) {
    stage.querySelectorAll('[data-slot]').forEach(el => {
      const slot = el.getAttribute('data-slot');
      if (slot && SLOT_CONFIG[slot]) applySlotStyle(el, slot);
    });
  }

  // Swap a surrounding card into the center and vice versa.
  // rAF ensures the browser reads the current inline transform before we
  // write the destination value — guaranteeing the transition fires.
  function swapSlots(stage, clickedSlot) {
    const center  = stage.querySelector('[data-slot="center"]');
    const clicked = stage.querySelector(`[data-slot="${clickedSlot}"]`);
    if (!center || !clicked) return;
    const wasCenter  = center.getAttribute('data-slot');
    const wasClicked = clicked.getAttribute('data-slot');
    requestAnimationFrame(() => {
      applySlotStyle(center,  wasClicked);
      applySlotStyle(clicked, wasCenter);
      // Re-init scroll buttons after swap so the new center card is wired up
      initCardScroll(center);
      initCardScroll(clicked);
    });
  }

  // ── Consent gate ───────────────────────────────────────────────────────────
  // Instagram cards: NEVER gated — content is stored locally in your CMS,
  //   no personal data is transmitted to Instagram's servers.
  // LinkedIn / other external: gated until social cookie accepted.
  //
  // Uses .needs-consent class (not .is-allowed) to match updated CSS.
  const applySocialGate = () => {
    const consent  = window.__cookieConsent?.read?.() ?? {};
    const socialOk = consent.social === true;

    document.querySelectorAll('[data-social-card]').forEach((card) => {
      const source = (card.getAttribute('data-source') || '').toLowerCase().trim();

      if (source === 'instagram') {
        // Always ungated — no matter what the cookie says
        card.classList.remove('needs-consent');
        return;
      }
      // linkedin and any other external source
      card.classList.toggle('needs-consent', !socialOk);
    });
  };

  applySocialGate(); // run immediately so Instagram cards are never blocked
  window.addEventListener('cookie-consent:changed', applySocialGate);

  // Individual "Accept" buttons inside each gated card
  document.querySelectorAll('[data-social-accept]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      window.__cookieConsent?.write?.({ social: true });
      // applySocialGate fires via cookie-consent:changed event above
    });
  });

  // ── Scroll-down button inside cards ───────────────────────────────────────
  // Hides the button when scroller reaches the bottom
  // (the "Show original post" link becomes the bottom CTA instead).
  function initCardScroll(card) {
    const scroller = card.querySelector('[data-tt-scroll]');
    const btnDown  = card.querySelector('[data-tt-down]');
    if (!scroller || !btnDown) return;

    // Remove old listener before adding new one (safe to call after swap)
    const newBtn = btnDown.cloneNode(true);
    btnDown.parentNode.replaceChild(newBtn, btnDown);

    const refresh = () => {
      const max   = scroller.scrollHeight - scroller.clientHeight;
      const atEnd = max <= 2 || scroller.scrollTop >= max - 2;
      newBtn.style.display = atEnd ? 'none' : 'grid';
    };
    newBtn.addEventListener('click', () => {
      scroller.scrollBy({ top: Math.round(scroller.clientHeight * 0.8), behavior: 'smooth' });
    });
    scroller.addEventListener('scroll', refresh, { passive: true });
    refresh();
  }
  document.querySelectorAll('[data-social-card]').forEach(initCardScroll);


  // ── Single [data-tt] forEach ─────────────────────────────────────────────
  // ONE loop. ONE click listener per stage.
  // Handles: confirm dialog / parallax / click-to-swap.
  document.querySelectorAll('[data-tt]').forEach((stage) => {
    const rig       = stage.querySelector('.tt-rig');
    const confirmEl = stage.querySelector('[data-tt-confirm]');
    const btnCancel = stage.querySelector('[data-tt-confirm-cancel]');
    const btnLeave  = stage.querySelector('[data-tt-confirm-leave]');
    if (!rig) return;

    // Seed all inline transforms immediately
    initSlotStyles(stage);

    // ── Confirm dialog ─────────────────────────────────────────────────────
    let pendingUrl = null;
    const openConfirm = (url) => {
      pendingUrl = url;
      confirmEl?.classList.remove('hidden');
      confirmEl?.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('overflow-hidden');
      document.body.classList.add('overflow-hidden');
    };
    const closeConfirm = () => {
      pendingUrl = null;
      confirmEl?.classList.add('hidden');
      confirmEl?.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('overflow-hidden');
      document.body.classList.remove('overflow-hidden');
    };
    btnCancel?.addEventListener('click', closeConfirm);
    confirmEl?.addEventListener('click', (e) => { if (e.target === confirmEl) closeConfirm(); });
    btnLeave?.addEventListener('click', () => {
      window.__cookieConsent?.write?.({ social: true });
      if (pendingUrl) window.open(pendingUrl, '_blank', 'noopener,noreferrer');
      closeConfirm();
    });

    // ── Parallax ───────────────────────────────────────────────────────────
    // BASF direction (confirmed from your description):
    //   Mouse moves RIGHT → cards appear to move LEFT
    //   Mouse moves LEFT  → cards appear to move RIGHT
    //   Mouse moves UP    → cards appear to move DOWN
    //   Mouse moves DOWN  → cards appear to move UP
    //
    // Parallax physics: when you look through a camera and pan right,
    // objects appear to move left. Same principle here.
    // tx = +1 when cursor is at right edge.
    // For cards to appear moving LEFT when cursor moves RIGHT:
    //   rotateY must be NEGATIVE when tx is POSITIVE  → rotateY = -(tx * factor)
    //   translate also moves in the opposite direction → moveX  = -(tx * factor)
    //
    // "Keeps last position when mouse leaves" → tx/ty are NOT reset on mouseleave.
    // They only update on mousemove. One final frame is painted on leave.

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion) {
      let raf = null;
      let tx  = 0;
      let ty  = 0;

      const paintRig = () => {
        // Negate tx and ty so rig moves OPPOSITE to cursor direction
        const rotY  = -(tx * 12);  // cursor right → negative rotateY → cards drift left
        const rotX  =  (ty *  8);  // cursor down  → positive rotateX → cards drift up
        const moveX = -(tx * 22);  // reinforces parallax: rig shifts opposite to cursor
        const moveY =  (ty * 14);

        rig.style.setProperty('--tt-mx', `${moveX}px`);
        rig.style.setProperty('--tt-my', `${moveY}px`);
        rig.style.setProperty('--tt-rx', `${rotX}deg`);
        rig.style.setProperty('--tt-ry', `${rotY}deg`);
      };

      stage.addEventListener('mousemove', (e) => {
        const r = stage.getBoundingClientRect();
        // Normalise: 0 at centre, range -1..+1
        tx = clamp((e.clientX - r.left) / r.width  * 2 - 1, -1, 1);
        ty = clamp((e.clientY - r.top)  / r.height * 2 - 1, -1, 1);
        if (!raf) raf = requestAnimationFrame(() => { raf = null; paintRig(); });
      });

      // On mouseenter: immediately sync tx/ty to current cursor position
      // so there's no "snap" from a stale position when re-entering.
      stage.addEventListener('mouseenter', (e) => {
        const r = stage.getBoundingClientRect();
        tx = clamp((e.clientX - r.left) / r.width  * 2 - 1, -1, 1);
        ty = clamp((e.clientY - r.top)  / r.height * 2 - 1, -1, 1);
        paintRig();
      });

      // mouseleave: keep last tx/ty — do NOT reset to zero.
      // The stage holds the last parallax state until the mouse re-enters.
      stage.addEventListener('mouseleave', () => {
        if (raf) { cancelAnimationFrame(raf); raf = null; }
        paintRig(); // ensure last state is rendered
      });

      paintRig(); // identity on mount
    }

    // ── Unified click handler (capture phase) ──────────────────────────────
    // Capture phase runs before any child handler, so transformed-card
    // hit-boxes are resolved against the stage coordinate space correctly.
    stage.addEventListener('click', (e) => {

      // Priority 1: "Show original post" link → open confirm overlay
      const origLink = e.target.closest('[data-tt-original]');
      if (origLink) {
        e.preventDefault();
        e.stopPropagation();
        const url = origLink.getAttribute('data-url') || origLink.getAttribute('href');
        if (url && url !== '#') openConfirm(url);
        return; // do NOT trigger swap
      }

      // Priority 2: other interactive elements → pass through normally
      if (e.target.closest(
        'a, button, input, textarea, select, ' +
        '[data-social-accept], [data-tt-down], [data-tt-confirm]'
      )) return;

      // Priority 3: click on a non-center slot card → swap to center
      const card = e.target.closest('[data-slot]');
      if (!card) return;
      const slot = card.getAttribute('data-slot');
      if (!slot || slot === 'center') return;
      swapSlots(stage, slot);

    }, true); // capture phase

  }); // end [data-tt] forEach

}); // end DOMContentLoaded