// resources/js/home.js

document.addEventListener('DOMContentLoaded', () => {

  // ─── Cookie consent shim ────────────────────────────────────────
  // Replace with your real cookie API if available.
  // Falls back to localStorage so consent persists across page loads.
  if (!window.__cookieConsent) {
    const KEY = 'gt_cookie_consent';
    window.__cookieConsent = {
      read()  {
        try { return JSON.parse(localStorage.getItem(KEY) || 'null') || {}; }
        catch { return {}; }
      },
      write(prefs) {
        try {
          const next = { ...this.read(), ...prefs };
          localStorage.setItem(KEY, JSON.stringify(next));
          window.dispatchEvent(new CustomEvent('cookie-consent:changed', { detail: next }));
        } catch {}
      },
    };
  }

  // ─── Industries slider ──────────────────────────────────────────
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

  // ─── Hero: video autoplay fallback ──────────────────────────────
  document.querySelectorAll('[data-hero]').forEach((section) => {
    const video  = section.querySelector('video');
    const poster = section.querySelector('[data-hero-poster]');
    if (!video || !poster) return;
    const p = video.play?.();
    if (p?.then) p.catch(() => { video.style.display = 'none'; poster.style.display = 'block'; });
  });


  // ═══════════════════════════════════════════════════════════════
  //  TRENDING TOPICS
  // ═══════════════════════════════════════════════════════════════

  function clamp(n, lo, hi) { return Math.max(lo, Math.min(hi, n)); }

  // ─── SLOT_CONFIG ─────────────────────────────────────────────────
  // These values MUST mirror the CSS translate3d offsets in home.css.
  // tx/ty/tz  = the second translate3d's x/y/z values (px)
  // rx        = optional rotateX value (deg)
  // scale     = uniform scale
  // z / op    = z-index / opacity
  //
  // When JS initialises, it writes each card's full transform as an
  // inline style — so CSS  transition  has a numeric "from" value
  // to animate from when a swap happens.
  const SLOT_CONFIG = {
    leftTop:    { tx: -330, ty: -155, tz: -200, rx: 0, scale: 0.700, z:  2, op: 0.80 },
    leftBottom: { tx: -460, ty:  172, tz: -240, rx: 0, scale: 0.700, z:  1, op: 0.74 },
    center:     { tx:    0, ty:   50, tz:    0, rx: 0, scale: 1,     z: 10, op: 1.00 },
    rightTop:   { tx:  310, ty: -178, tz: -165, rx: 3, scale: 0.700, z:  3, op: 0.80 },
    rightBottom:{ tx:  340, ty:  182, tz: -200, rx: 3, scale: 0.700, z:  2, op: 0.74 },
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

  // Apply a slot's full visual state as inline styles on the element.
  // Inline styles take precedence over class rules, so the CSS
  // transition interpolates between the inline transform values.
  function applySlotStyle(el, slotName) {
    const c = SLOT_CONFIG[slotName];
    if (!c) return;
    ALL_SLOTS.forEach(s => el.classList.remove(`tt-slot--${s}`));
    el.classList.add(`tt-slot--${slotName}`);
    el.setAttribute('data-slot', slotName);
    el.style.transform = buildTransform(slotName);
    el.style.zIndex    = c.z;
    el.style.opacity   = c.op;
    el.style.cursor    = slotName === 'center' ? 'default' : 'pointer';
  }

  // Seed inline transforms on page load so the first swap has a "from" value.
  function initSlotStyles(stage) {
    stage.querySelectorAll('[data-slot]').forEach(el => {
      const slot = el.getAttribute('data-slot');
      if (slot && SLOT_CONFIG[slot]) applySlotStyle(el, slot);
    });
  }

  // Swap a surrounding card with the center card.
  // rAF ensures the browser registers the current inline transform
  // before we write the destination — guaranteeing the transition fires.
  function swapSlots(stage, clickedSlot) {
    const center  = stage.querySelector('[data-slot="center"]');
    const clicked = stage.querySelector(`[data-slot="${clickedSlot}"]`);
    if (!center || !clicked) return;

    const wasCenter  = center.getAttribute('data-slot');   // 'center'
    const wasClicked = clicked.getAttribute('data-slot');  // e.g. 'leftTop'

    requestAnimationFrame(() => {
      applySlotStyle(center,  wasClicked);   // center card → side position
      applySlotStyle(clicked, wasCenter);    // side card   → center position
    });
  }

  // ─── Consent gate ────────────────────────────────────────────────
  // Called on load and whenever cookie-consent:changed fires.
  // Instagram cards are NEVER gated (content served locally, no
  // external data transfer). LinkedIn and others need consent.
  const applySocialGate = () => {
    const consent      = window.__cookieConsent?.read?.() ?? {};
    const socialOk     = consent.social === true;

    document.querySelectorAll('[data-social-card]').forEach((card) => {
      const source = (card.getAttribute('data-source') || '').toLowerCase().trim();

      if (source === 'instagram') {
        // Always remove gate for Instagram regardless of consent state
        card.classList.remove('needs-consent');
        return;
      }
      // LinkedIn / other external sources: gate until social cookie accepted
      card.classList.toggle('needs-consent', !socialOk);
    });
  };

  // Run immediately so Instagram cards are never blanked,
  // even if the event fires before DOMContentLoaded finishes.
  applySocialGate();
  window.addEventListener('cookie-consent:changed', applySocialGate);

  // Individual "Accept" button inside each gated card
  document.querySelectorAll('[data-social-accept]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      window.__cookieConsent?.write?.({ social: true });
      // applySocialGate() fires via the cookie-consent:changed event above
    });
  });

  // ─── Scroll-down button inside each card ─────────────────────────
  // Hides the button when the scroller reaches the bottom
  // (the "Show original post" link becomes visible).
  function initCardScroll(card) {
    const scroller = card.querySelector('[data-tt-scroll]');
    const btnDown  = card.querySelector('[data-tt-down]');
    if (!scroller || !btnDown) return;

    const refresh = () => {
      const max   = scroller.scrollHeight - scroller.clientHeight;
      const atEnd = max <= 2 || scroller.scrollTop >= max - 2;
      btnDown.style.display = atEnd ? 'none' : 'grid';
    };

    btnDown.addEventListener('click', () => {
      scroller.scrollBy({ top: Math.round(scroller.clientHeight * 0.80), behavior: 'smooth' });
    });
    scroller.addEventListener('scroll', refresh, { passive: true });
    refresh(); // initial check
  }

  document.querySelectorAll('[data-social-card]').forEach(initCardScroll);

  // ─── Single [data-tt] loop ───────────────────────────────────────
  // ONE forEach. ONE click listener per stage.
  // Handles: confirm dialog / parallax motion / click-to-swap.
  document.querySelectorAll('[data-tt]').forEach((stage) => {
    const rig       = stage.querySelector('.tt-rig');
    const confirmEl = stage.querySelector('[data-tt-confirm]');
    const btnCancel = stage.querySelector('[data-tt-confirm-cancel]');
    const btnLeave  = stage.querySelector('[data-tt-confirm-leave]');
    if (!rig) return;

    // Seed inline transforms immediately
    initSlotStyles(stage);

    // ── Confirm overlay ────────────────────────────────────────────
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

    // ── Parallax mouse tracking ────────────────────────────────────
    // BASF parallax direction (confirmed from your description):
    //   mouse right  → cards move LEFT  → rig translates LEFT  + rotateY negative
    //   mouse left   → cards move RIGHT → rig translates RIGHT + rotateY positive
    //   mouse up     → cards move DOWN  → rig translates DOWN  + rotateX positive
    //   mouse down   → cards move UP    → rig translates UP    + rotateX negative
    //
    // tx/ty are normalised cursor position: 0 at centre, +1 at right/bottom.
    // To make cards move OPPOSITE to the cursor:
    //   rotateY  = -(tx * factor)   mouse right (tx>0) → negative rotateY → cards appear left
    //   rotateX  = +(ty * factor)   mouse down  (ty>0) → positive rotateX → cards appear up
    //   translate = SAME sign as rotation for subtle drift reinforcement
    //
    // "Last movement state when pointer leaves" → we keep tx/ty at their last values
    // and only reset on mouseenter (not mouseleave).

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!prefersReducedMotion) {
      let raf = null;
      let tx  = 0;
      let ty  = 0;
      let hasEnteredOnce = false;

      const applyParallax = () => {
        // BASF direction: negate tx for rotateY so cards drift opposite to cursor
        const rotY  = -(tx * 10);    // mouse right → rotateY negative → cards left
        const rotX  =  (ty *  7);    // mouse down  → rotateX positive → cards up
        const moveX = -(tx * 18);    // reinforce: rig drifts against cursor
        const moveY =  (ty * 12);

        rig.style.transform =
          `translate3d(${moveX}px,${moveY}px,0px) ` +
          `rotateX(${rotX}deg) ` +
          `rotateY(${rotY}deg)`;
      };

      // On mouseenter: update tx/ty to current position immediately,
      // so there's no jump from the last-state when re-entering
      stage.addEventListener('mouseenter', (e) => {
        const r = stage.getBoundingClientRect();
        tx = clamp((e.clientX - r.left)  / r.width  * 2 - 1, -1, 1);
        ty = clamp((e.clientY - r.top)   / r.height * 2 - 1, -1, 1);
        hasEnteredOnce = true;
        applyParallax();
      });

      stage.addEventListener('mousemove', (e) => {
        const r = stage.getBoundingClientRect();
        tx = clamp((e.clientX - r.left)  / r.width  * 2 - 1, -1, 1);
        ty = clamp((e.clientY - r.top)   / r.height * 2 - 1, -1, 1);
        if (!raf) {
          raf = requestAnimationFrame(() => { raf = null; applyParallax(); });
        }
      });

      // mouseleave: keep last tx/ty — do NOT reset to zero.
      // The stage retains the last parallax state until mouse re-enters.
      // (This matches the BASF behaviour described.)
      // Only apply one last frame to ensure the stored tx/ty is rendered:
      stage.addEventListener('mouseleave', () => {
        if (raf) { cancelAnimationFrame(raf); raf = null; }
        applyParallax(); // render the last position one more time
      });

      applyParallax(); // set identity on mount
    }

    // ── Unified click handler (capture phase) ──────────────────────
    stage.addEventListener('click', (e) => {
      // Priority 1: "Show original post" anchor → confirm overlay
      const origLink = e.target.closest('[data-tt-original]');
      if (origLink) {
        e.preventDefault();
        e.stopPropagation();
        const url = origLink.getAttribute('data-url') || origLink.getAttribute('href');
        if (url && url !== '#') openConfirm(url);
        return;
      }

      // Priority 2: interactive elements → let them handle themselves
      if (e.target.closest(
        'a, button, input, textarea, select, ' +
        '[data-social-accept], [data-tt-down], [data-tt-confirm]'
      )) return;

      // Priority 3: click on a non-center slot → swap with center
      const card = e.target.closest('[data-slot]');
      if (!card) return;
      const slot = card.getAttribute('data-slot');
      if (!slot || slot === 'center') return;

      swapSlots(stage, slot);

    }, true); // capture phase: runs before child handlers

  }); // end [data-tt] forEach

}); // end DOMContentLoaded