// resources/js/home.js

document.addEventListener('DOMContentLoaded', () => {

  // ─── Cookie consent shim ──────────────────────────────────────────────────
  // Provides window.__cookieConsent if your app hasn't defined it yet.
  // Replace with your real cookie API. Persists in localStorage.
  if (!window.__cookieConsent) {
    const _K = 'gt_cookie_consent';
    window.__cookieConsent = {
      read() {
        try { return JSON.parse(localStorage.getItem(_K) || 'null') || {}; }
        catch { return {}; }
      },
      write(prefs) {
        try {
          const next = { ...this.read(), ...prefs };
          localStorage.setItem(_K, JSON.stringify(next));
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
    leftTop:    { tx: -300, ty: -100, tz: -240, rx: 0, scale: 0.672, driftX: 10, driftY: 8,  driftZ: 10 },
    leftBottom: { tx: -350, ty:  305, tz: -280, rx: 0, scale: 0.672, driftX: 8,  driftY: 7,  driftZ: 8 },
    center:     { tx:    0, ty: -40, tz:    0, rx: 5, scale: 1,     driftX: 34, driftY: 26, driftZ: 34 },
    rightTop:   { tx:  310, ty: -70, tz: -180, rx: 0, scale: 0.672, driftX: 11, driftY: 8,  driftZ: 11 },
    rightBottom:{ tx:  350, ty:  325, tz: -220, rx: 0, scale: 0.672, driftX: 9,  driftY: 7,  driftZ: 9 },
  };

  /*driftX: 10, driftY: 8,  driftZ: 10 },
    leftBottom:  { tx: -380, ty:  205, tz: -280, rx: 0, scale: 0.672, driftX: 8,  driftY: 7,  driftZ: 8  },
    center:      { tx:    0, ty: -150, tz:    0, rx: 5, scale: 1,     driftX: 34, driftY: 26, driftZ: 34 },
    rightTop:    { tx:  340, ty: -170, tz: -180, rx: 0, scale: 0.672, driftX: 11, driftY: 8,  driftZ: 11 },
    rightBottom: { tx:  370, ty:  225, tz: -220, rx: 0, scale: 0.672, driftX: 9,  driftY: 7,  driftZ: 9  }, */

  // Parallax depth multiplier per slot.
  // Center (tz=0, closest) = 1.0 → moves the most.
  // Deep side slots (tz=-240 to -280) = 0.40-0.55 → move less.
  // This creates the depth/parallax illusion without any Z-rotation.
  const ZMUL = {
    leftTop: 0.12,
    leftBottom: 0.08,
    center: 1.15,
    rightTop: 0.14,
    rightBottom: 0.10,
  };
 
  // Maximum XY drift amplitude in px (at |tx|=1, |ty|=1).
  // Large enough to see clearly, not so large cards leave the stage.
  const AMP_X = 23;
  const AMP_Y = 12;
  const ALL_SLOTS = Object.keys(SLOT_CONFIG);

  // Build the full CSS transform string for a given slot name.
    function buildTransform(slotName, tx_cursor = 0, ty_cursor = 0) {
    const c = SLOT_CONFIG[slotName];
    if (!c) return '';

    // opposite-to-cursor motion
    const dx = -(tx_cursor * c.driftX);
    const dy = -(ty_cursor * c.driftY);

    // center card gets much stronger Z reaction, side cards stay subtle
    const dz = -((Math.abs(tx_cursor) + Math.abs(ty_cursor)) * 0.5 * c.driftZ);

    return (
      `translate3d(-50%,50%,0px) ` +
      `translate3d(${c.tx + dx}px,${c.ty + dy}px,${c.tz + dz}px) ` +
      (c.rx ? `rotateX(${c.rx}deg) ` : '') +
      `scale(${c.scale})`
    );
  }

  // Apply a slot's full visual state as inline styles.
  // Inline style beats class rule → CSS transition can interpolate between values.
    function applySlotStyle(slot, slotName, tx_cursor = 0, ty_cursor = 0) {
    const c = SLOT_CONFIG[slotName];
    if (!c) return;

    ALL_SLOTS.forEach(s => slot.classList.remove(`tt-slot--${s}`));
    slot.classList.add(`tt-slot--${slotName}`);
    slot.setAttribute('data-slot', slotName);

    slot.style.transform = buildTransform(slotName, tx_cursor, ty_cursor);
    slot.style.cursor = slotName === 'center' ? 'default' : 'pointer';
  }

  // Seed inline transforms on all cards so the first swap has a "from" value.
    function initSlotStyles(stage, tx_cursor = 0, ty_cursor = 0) {
    stage.querySelectorAll('[data-slot]').forEach(el => {
      const slot = el.getAttribute('data-slot');
      if (slot && SLOT_CONFIG[slot]) {
        applySlotStyle(el, slot, tx_cursor, ty_cursor);
      }
    });
  }

  // Swap a surrounding card into the center and vice versa.
  // rAF ensures the browser reads the current inline transform before we
  // write the destination value — guaranteeing the transition fires.
    function swapSlots(stage, clickedSlotName, tx_cursor = 0, ty_cursor = 0) {
    const centerE1  = stage.querySelector('[data-slot="center"]');
    const clickedE1 = stage.querySelector(`[data-slot="${clickedSlotName}"]`);
    if (!centerE1 || !clickedE1) return;

    const wasCenter  = centerE1.getAttribute('data-slot');
    const wasClicked = clickedE1.getAttribute('data-slot');

    requestAnimationFrame(() => {
      applySlotStyle(centerE1, wasClicked, tx_cursor, ty_cursor);
      applySlotStyle(clickedE1, wasCenter, tx_cursor, ty_cursor);
      initCardScroll(centerE1);
      initCardScroll(clickedE1);
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
      const src = (card.getAttribute('data-source') || '').toLowerCase().trim();
      if (src ==='instagram') { card.classList.remove('needs-consent'); return; }
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
  function initCardScroll(slotOrcard) {
    const scroller = slotOrcard.querySelector('[data-tt-scroll]');
    const btnDown  = slotOrcard.querySelector('[data-tt-down]');
    if (!scroller || !btnDown) return;

    // Remove old listener before adding new one (safe to call after swap)
    const fresh = btnDown.cloneNode(true);
    btnDown.replaceWith(fresh);

    const refresh = () => {
      const max   = scroller.scrollHeight - scroller.clientHeight;
      const atEnd = max <= 2 || scroller.scrollTop >= max - 2;
      fresh.style.display = atEnd ? 'none' : 'grid';
    };
    fresh.addEventListener('click', () => {
      scroller.scrollBy({ top: Math.round(scroller.clientHeight * 0.8), behavior: 'smooth' });
    });
    scroller.addEventListener('scroll', refresh, { passive: true });
    refresh();
  }
  document.querySelectorAll('[data-slot]').forEach(initCardScroll);


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
    initSlotStyles(stage, 0, 0);

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
        const rotY  = -(tx * 1.35);  // cursor right → negative rotateY → cards drift left
        const rotX  =  (ty *  1.15);  // cursor down  → positive rotateX → cards drift up
        const moveX = -(tx * 70);  // reinforces parallax: rig shifts opposite to cursor
        const moveY =  (ty * 70 * -1);

        rig.style.transform =
          `translate3d(${moveX}px,${moveY}px,0px) ` +
          `rotateX(${rotX}deg) ` +
          `rotateY(${rotY}deg)`;

        // important: slot-level drift difference
        stage.querySelectorAll('.tt-slot').forEach((slotEl) => {
          const slotName = slotEl.getAttribute('data-slot');
          if (!slotName) return;
          slotEl.style.transform = buildTransform(slotName, tx, ty);
        });
      };

      const onMove = (e) => {
        const r = stage.getBoundingClientRect();
        tx = clamp((e.clientX - r.left) / r.width  * 2 - 1, -1, 1);
        ty = clamp((e.clientY - r.top)  / r.height * 2 - 1, -1, 1);
        if (!raf) raf = requestAnimationFrame(() => { raf = null; paintRig(); });
      };

      // On mouseenter: immediately sync tx/ty to current cursor position
      // so there's no "snap" from a stale position when re-entering.
      stage.addEventListener('mouseenter', (e) => {
        const r = stage.getBoundingClientRect();
        tx = clamp((e.clientX - r.left) / r.width  * 2 - 1, -1, 1);
        ty = clamp((e.clientY - r.top)  / r.height * 2 - 1, -1, 1);
        paintRig();
      });

      stage.addEventListener('mousemove', onMove);

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

      // Priority 0: confirm dialog must always work normally
      if (e.target.closest('[data-tt-confirm-cancel]') || e.target.closest('[data-tt-confirm-leave]')) {
        return;
      }

      // Priority 1: "Show original post" link
      const origLink = e.target.closest('[data-tt-original]');
      if (origLink) {
        const ownerSlot = origLink.closest('[data-slot]');
        const ownerSlotName = ownerSlot?.getAttribute('data-slot');

        // Only center card may open confirm dialog
        if (ownerSlotName === 'center') {
          e.preventDefault();
          e.stopPropagation();
          const url = origLink.getAttribute('data-url') || origLink.getAttribute('href');
          if (url && url !== '#') openConfirm(url);
          return;
        }

        // Surrounding card: swap only
        e.preventDefault();
        e.stopPropagation();
        if (ownerSlotName && ownerSlotName !== 'center') {
          swapSlots(stage, ownerSlotName, typeof tx !== 'undefined' ? tx : 0, typeof ty !== 'undefined' ? ty : 0);
        }
        return;
      }

      // Priority 2: Accept / privacy policy / other inner controls
      const interactiveEl = e.target.closest(
        'a, button, input, textarea, select, [data-social-accept], [data-tt-down]'
      );

      if (interactiveEl) {
        const ownerSlot = interactiveEl.closest('[data-slot]');
        const ownerSlotName = ownerSlot?.getAttribute('data-slot');

        // Center card: allow normal behavior
        if (ownerSlotName === 'center') {
          return;
        }

        // Outside cards: swap only
        e.preventDefault();
        e.stopPropagation();
        if (ownerSlotName && ownerSlotName !== 'center') {
          swapSlots(stage, ownerSlotName, typeof tx !== 'undefined' ? tx : 0, typeof ty !== 'undefined' ? ty : 0);
        }
        return;
      }

      // Priority 3: click on a non-center slot card → swap to center
      const slot = e.target.closest('[data-slot]');
      if (!slot) return;

      const slotName = slot.getAttribute('data-slot');
      if (!slotName || slotName === 'center') return;

      swapSlots(stage, slotName, typeof tx !== 'undefined' ? tx : 0, typeof ty !== 'undefined' ? ty : 0);

    }, true);

  }); // end [data-tt] forEach

}); // end DOMContentLoaded