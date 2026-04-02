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
});