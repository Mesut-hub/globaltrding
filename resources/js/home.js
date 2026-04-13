// resources/js/home.js
document.addEventListener('DOMContentLoaded', () => {
  // Minimal __cookieConsent shim.
  // If your app already defines window.__cookieConsent, this block is skipped.
  if (!window.__cookieConsent) {
    const STORAGE_KEY = 'gt_cookie_consent';
    window.__cookieConsent = {
      read() {
        try {
          return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null') || {};
        } catch { return {}; }
      },
      write(prefs) {
        try {
          const current = this.read();
          const next = { ...current, ...prefs };
          localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
          window.dispatchEvent(new CustomEvent('cookie-consent:changed', { detail: next }));
        } catch {}
      },
    };
  }
  // INDUSTRIES SLIDER
  document.querySelectorAll('[data-industry-slider]').forEach((root) => {
    const track = root.querySelector('[data-ind="track"]');
    const prev = root.querySelector('[data-ind="prev"]');
    const next = root.querySelector('[data-ind="next"]');

    if (!track || !prev || !next) return;

    const step = () => Math.min(track.clientWidth * 0.9, 520);

    const updateNavState = () => {
      // allow a tiny epsilon due to fractional pixels
      const eps = 2;

      const atStart = track.scrollLeft <= eps;
      const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - eps;

      prev.disabled = atStart;
      next.disabled = atEnd;

      prev.classList.toggle('is-disabled', atStart);
      next.classList.toggle('is-disabled', atEnd);

      // Fade edges
      root.classList.toggle('at-start', atStart);
      root.classList.toggle('at-end', atEnd);
    };

    prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));

    track.addEventListener('scroll', () => {
      // cheap throttle via rAF
      if (track._raf) return;
      track._raf = requestAnimationFrame(() => {
        track._raf = null;
        updateNavState();
      });
    }, { passive: true });

    // initial
    updateNavState();

    // update on resize (layout changes)
    window.addEventListener('resize', () => updateNavState(), { passive: true });
  });

  // HERO: if autoplay fails, hide video and show poster fallback
  document.querySelectorAll('[data-hero]').forEach((section) => {
    const video = section.querySelector('video');
    const poster = section.querySelector('[data-hero-poster]');
    if (!video || !poster) return;

    const p = video.play?.();
    if (p && typeof p.then === 'function') {
      p.catch(() => {
        video.style.display = 'none';
        poster.style.display = 'block';
      });
    }
  });

  // TRENDING TOPICS: consent gate (social)
  const applySocialGate = () => {
    const consent = window.__cookieConsent?.read?.() ?? { social: null };
    const socialAllowed = consent.social === true;

    document.querySelectorAll('[data-social-card]').forEach((card) => {
      const source = card.dataset.source || '';
      // Instagram never needs consent (no external data transfer)
      if (source === 'instagram') {
        card.classList.remove('needs-consent');
        return;
      }
      // LinkedIn (and any other external source) needs consent
      card.classList.toggle('needs-consent', !socialAllowed);
    });
  };

  applySocialGate();
  window.addEventListener('cookie-consent:changed', applySocialGate);

  document.querySelectorAll('[data-social-accept]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      window.__cookieConsent?.write?.({ social: true });
    });
  });

  // ===== TRENDING TOPICS: BASF-like interactions =====
  function clamp(n, a, b) { return Math.max(a, Math.min(b, n)); }

  // Scroll/down-arrow logic inside cards
  function initCardScroll(card) {
    const scroller = card.querySelector('[data-tt-scroll]');
    const btnDown = card.querySelector('[data-tt-down]');
    if (!scroller || !btnDown) return;

    const refresh = () => {
      const max = scroller.scrollHeight - scroller.clientHeight;
      const atEnd = max <= 2 || scroller.scrollTop >= max - 2;
      btnDown.style.display = atEnd ? 'none' : 'grid';
    };

    btnDown.addEventListener('click', () => {
      scroller.scrollBy({ top: Math.round(scroller.clientHeight * 0.85), behavior: 'smooth' });
    });

    scroller.addEventListener('scroll', refresh, { passive: true });
    refresh();
  }

  document.querySelectorAll('[data-social-card]').forEach(initCardScroll);

  // Swap-to-center logic
  // Static position data for each slot name.
  // Values mirror the static translate3d offsets in home.css (Fix 1).
  // Add z-index and scale so those also transition when swapping.
  const SLOT_POSITIONS = {
    leftTop:     { tx: -530, ty: -310, tz: -240, s: 'var(--tt-card-scale-sm)', z: 2,  op: 0.8 },
    leftBottom:  { tx: -630, ty:  220, tz: -280, s: 'var(--tt-card-scale-sm)', z: 1,  op: 0.8 },
    center:      { tx:    0, ty:  100, tz:    0, s: '1',                        z: 10, op: 1.0 },
    rightTop:    { tx:  400, ty: -250, tz: -180, s: 'var(--tt-card-scale-sm)', z: 3,  op: 0.8 },
    rightBottom: { tx:  430, ty:  350, tz: -220, s: 'var(--tt-card-scale-sm)', z: 2,  op: 0.8 },
  };

  // Apply a slot's computed style directly as inline vars + transform.
  // This gives CSS transition something concrete to interpolate.
  function applySlotStyle(el, slotName) {
    const SLOTS = ['leftTop','leftBottom','center','rightTop','rightBottom'];
    const p = SLOT_POSITIONS[slotName];
    if (!p) return;

    SLOTS.forEach(s => el.classList.remove(`tt-slot--${s}`));
    el.classList.add(`tt-slot--${slotName}`);
    el.setAttribute('data-slot', slotName);

    const isCursor = slotName === 'center';
    el.style.cursor = isCursor ? 'default' : 'pointer';
    el.style.zIndex  = p.z;
    el.style.opacity = p.op;
    el.style.transform =
      `translate3d(-50%, -50%, 0px) ` +
      `translate3d(${p.tx}px, ${p.ty}px, ${p.tz}px) ` +
      `scale(${p.s})`;
  }

  // Call applySlotStyle on every card at init to seed inline transforms
  // so the first swap has a starting value to animate FROM.
  function initSlotStyles(stage) {
    stage.querySelectorAll('[data-slot]').forEach(el => {
      applySlotStyle(el, el.getAttribute('data-slot'));
    });
  }

  function swapSlots(stage, fromSlot) {
    const center = stage.querySelector('[data-slot="center"]');
    const other  = stage.querySelector(`[data-slot="${fromSlot}"]`);
    if (!center || !other) return;

    const oldCenterSlot = center.getAttribute('data-slot'); // 'center'
    const oldOtherSlot  = other.getAttribute('data-slot');  // e.g. 'leftTop'

    // Give the browser one frame to read the current inline transform
    // before we write the destination — ensures the transition fires.
    requestAnimationFrame(() => {
      applySlotStyle(center, oldOtherSlot);
      applySlotStyle(other,  oldCenterSlot);
    });
  }

  function isInteractiveTarget(t) {
    return !!t.closest('a,button,input,textarea,select,[data-social-accept],[data-tt-down],[data-tt-original]');
  }

  function markClickable(stage) {
    stage.querySelectorAll('[data-slot]').forEach((card) => {
      const slot = card.getAttribute('data-slot');
      card.dataset.clickable = slot && slot !== 'center' ? '1' : '0';
    });
  }

  document.querySelectorAll('[data-tt]').forEach((stage) => {
    const rig     = stage.querySelector('.tt-rig');
    const confirm = stage.querySelector('[data-tt-confirm]');
    const btnCancel = stage.querySelector('[data-tt-confirm-cancel]');
    const btnLeave  = stage.querySelector('[data-tt-confirm-leave]');

    if (!rig) return;

    initSlotStyles(stage);

    // ── Confirm dialog state ──────────────────────────────────────
    let pendingUrl = null;
    const openConfirm  = (url) => {
      pendingUrl = url;
      confirm?.classList.remove('hidden');
      confirm?.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('overflow-hidden');
    };
    const closeConfirm = () => {
      pendingUrl = null;
      confirm?.classList.add('hidden');
      confirm?.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('overflow-hidden');
    };

    btnCancel?.addEventListener('click', closeConfirm);
    confirm?.addEventListener('click', (e) => { if (e.target === confirm) closeConfirm(); });
    btnLeave?.addEventListener('click', () => {
      window.__cookieConsent?.write?.({ social: true });
      if (pendingUrl) window.open(pendingUrl, '_blank', 'noopener');
      closeConfirm();
    });

    // ── Parallax ──────────────────────────────────────────────────
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      // Still wire up click-to-swap even without motion
    } else {
      let raf = null, tx = 0, ty = 0;
      const setVars = () => {
        const rotY =  tx * 12;
        const rotX = -ty * 8;
        const moveX = tx * 24;
        const moveY = ty * 16;
        rig.style.transform =
          `translate3d(${moveX}px,${moveY}px,0px) ` +
          `rotateX(${rotX}deg) rotateY(${rotY}deg)`;
      };
      stage.addEventListener('mousemove', (e) => {
        const r = stage.getBoundingClientRect();
        tx = clamp((e.clientX - r.left)  / r.width  * 2 - 1, -1, 1);
        ty = clamp((e.clientY - r.top)   / r.height * 2 - 1, -1, 1);
        if (!raf) raf = requestAnimationFrame(() => { raf = null; setVars(); });
      });
      stage.addEventListener('mouseleave', () => { tx = 0; ty = 0; setVars(); });
      setVars();
    }

    // ── Single click handler — confirm OR swap, never both ────────
    stage.addEventListener('click', (e) => {
      // Priority 1: "Show original post" link → confirm dialog
      const origLink = e.target.closest('[data-tt-original]');
      if (origLink) {
        e.preventDefault();
        const url = origLink.dataset.url || origLink.getAttribute('href');
        if (url) openConfirm(url);
        return; // do NOT also swap
      }

      // Priority 2: any other interactive element → let it bubble naturally
      if (e.target.closest('a,button,input,textarea,select,[data-social-accept],[data-tt-down]')) {
        return;
      }

      // Priority 3: click on a non-center card → swap it to center
      const card = e.target.closest('[data-slot]');
      if (!card) return;
      const slot = card.getAttribute('data-slot');
      if (!slot || slot === 'center') return;
      swapSlots(stage, slot);
    }, true); // capture phase keeps transformed child coords consistent

  });
});