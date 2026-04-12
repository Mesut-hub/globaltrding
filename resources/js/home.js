// resources/js/home.js
document.addEventListener('DOMContentLoaded', () => {
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
    const consent = window.__cookieConsent?.read?.() || { social: null };
    const allowed = consent.social === true;

    document.querySelectorAll('[data-social-card]').forEach((card) => {
      card.classList.toggle('is-allowed', allowed);
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

  // Confirm overlay handling (Show original post)
  document.querySelectorAll('[data-tt]').forEach((stage) => {
    const confirm = stage.querySelector('[data-tt-confirm]');
    const btnCancel = stage.querySelector('[data-tt-confirm-cancel]');
    const btnLeave = stage.querySelector('[data-tt-confirm-leave]');
    if (!confirm || !btnCancel || !btnLeave) return;

    let pendingUrl = null;

    const openConfirm = (url) => {
      pendingUrl = url;
      confirm.classList.remove('hidden');
      confirm.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('overflow-hidden');
      document.body.classList.add('overflow-hidden');
    };

    const closeConfirm = () => {
      pendingUrl = null;
      confirm.classList.add('hidden');
      confirm.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('overflow-hidden');
      document.body.classList.remove('overflow-hidden');
    };

    stage.addEventListener('click', (e) => {
      const a = e.target.closest('[data-tt-original]');
      if (!a) return;
      e.preventDefault();
      const url = a.getAttribute('data-url') || a.getAttribute('href');
      if (url) openConfirm(url);
      // Don't swap when interacting with UI controls/links inside cards
      if (e.target.closest('a,button,input,textarea,[data-social-accept],[data-tt-down],[data-tt-original]')) {
        return;
      }
    });

    btnCancel.addEventListener('click', closeConfirm);
    confirm.addEventListener('click', (e) => { if (e.target === confirm) closeConfirm(); });

    btnLeave.addEventListener('click', () => {
      // Accept social consent (acts like the BASF acceptance behavior)
      window.__cookieConsent?.write?.({ social: true });
      if (pendingUrl) window.open(pendingUrl, '_blank', 'noopener');
      closeConfirm();
    });
  });

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
  function swapSlots(stage, fromSlot) {
    const center = stage.querySelector('[data-slot="center"]');
    const other = stage.querySelector(`[data-slot="${fromSlot}"]`);
    if (!center || !other) return;

    const from = other.getAttribute('data-slot');
    center.setAttribute('data-slot', from);
    other.setAttribute('data-slot', 'center');

    // Update classes to match slots
    const slots = ['leftTop','leftBottom','rightTop','rightBottom','center'];
    const applySlotClass = (el) => {
      slots.forEach(s => el.classList.remove(`tt-slot--${s}`));
      const s = el.getAttribute('data-slot');
      el.classList.add(`tt-slot--${s}`);
    };
    applySlotClass(center);
    applySlotClass(other);
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

  // Stage parallax direction fix + click-to-swap
  document.querySelectorAll('[data-tt]').forEach((stage) => {
    const rig = stage.querySelector('.tt-rig');
    if (!rig) return;

    // Respect reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    let raf = null;
    let tx = 0;
    let ty = 0;

    const setVars = () => {
      // Direction fix:
      // BASF feel: move mouse right => cards drift right; move mouse up => cards drift up.
      // Your previous logic created the opposite on your build; invert here.
      const x = -tx;
      const y = -ty;

      const rotY = x * 14;
      const rotX = -y * 10;
      const moveX = x * 30;
      const moveY = y * 22;

      rig.style.setProperty('--tt-mx', `${moveX}px`);
      rig.style.setProperty('--tt-my', `${moveY}px`);
      rig.style.setProperty('--tt-rx', `${rotX}deg`);
      rig.style.setProperty('--tt-ry', `${rotY}deg`);

      // ALSO set --tt-x/--tt-y for per-slot drift (CSS uses these)
      rig.style.setProperty('--tt-x', `${x}`);
      rig.style.setProperty('--tt-y', `${y}`);
    };

    const onMove = (e) => {
      const r = stage.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width;  // 0..1
      const y = (e.clientY - r.top) / r.height;  // 0..1

      tx = clamp((x - 0.5) * 2, -1, 1);
      ty = clamp((y - 0.5) * 2, -1, 1);

      if (!raf) {
        raf = requestAnimationFrame(() => {
          raf = null;
          setVars();
        });
      }
    };

    const reset = () => {
      tx = 0;
      ty = 0;
      setVars();
    };

    stage.addEventListener('mousemove', onMove);
    stage.addEventListener('mouseleave', reset);
    setVars();

    // Make swap reliable: attach handler to cards themselves
    const bindCardClicks = () => {
      stage.querySelectorAll('[data-slot]').forEach((card) => {
        if (card.__swapBound) return;
        card.__swapBound = true;
        card.addEventListener('click', (e) => {
          if (isInteractiveTarget(e.target)) return;
          const slot = card.getAttribute('data-slot');
          if (!slot || slot === 'center') return;
          swapSlots(stage, slot);
          markClickable(stage);
        });
      });
    };

    bindCardClicks();
    markClickable(stage);
  });
});