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

  // TRENDING TOPICS: playable tilt animation (like BASF subtle motion)
    // TRENDING TOPICS: BASF-like 3D rig (stage parallax)
  document.querySelectorAll('[data-tt]').forEach((stage) => {
    const rig = stage.querySelector('.tt-rig');
    if (!rig) return;

    // Respect reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    let raf = null;
    let tx = 0;
    let ty = 0;

    const apply = () => {
      raf = null;

      // stronger than before (BASF feel)
      const rotY = tx * 14;           // rotate stage
      const rotX = -ty * 10;
      const moveX = tx * 30;          // translate stage
      const moveY = ty * 22;

      rig.style.transform =
        `translate3d(${moveX}px, ${moveY}px, 0px) rotateX(${rotX}deg) rotateY(${rotY}deg)`;

      // Add slight “independent drift” per card based on depth
      stage.querySelectorAll('.tt-card').forEach((card) => {
        const z = card.classList.contains('tt-slot--center') ? 1.0 : 0.55;
        const cx = tx * 26 * z;
        const cy = ty * 18 * z;
        card.style.transform = card.style.transform.replace(/translate3d\([^)]*\)/, (m) => m)
        // We do NOT overwrite base transforms here; we add CSS variable offsets instead:
      });
    };

    // Use CSS variables to add offsets without destroying base slot transforms
    const setVars = () => {
      const rotY = tx * 14;
      const rotX = -ty * 10;
      const moveX = tx * 30;
      const moveY = ty * 22;

      rig.style.setProperty('--tt-mx', `${moveX}px`);
      rig.style.setProperty('--tt-my', `${moveY}px`);
      rig.style.setProperty('--tt-rx', `${rotX}deg`);
      rig.style.setProperty('--tt-ry', `${rotY}deg`);
    };

    const onMove = (e) => {
      const r = stage.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width;  // 0..1
      const y = (e.clientY - r.top) / r.height;  // 0..1

      tx = (x - 0.5) * 2; // -1..1
      ty = (y - 0.5) * 2;

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

    // init
    setVars();
  });
});