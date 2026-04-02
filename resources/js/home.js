// resources/js/home.js
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-industry-slider]').forEach((root) => {
    const track = root.querySelector('[data-ind="track"]');
    const prev = root.querySelector('[data-ind="prev"]');
    const next = root.querySelector('[data-ind="next"]');

    if (!track || !prev || !next) return;

    const step = () => Math.min(track.clientWidth * 0.9, 520);

    prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
  });
});

document.addEventListener('DOMContentLoaded', () => {
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