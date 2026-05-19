/**
 * UpLiftHub — Scroll Reveal Manager
 * Lightweight scroll-driven animation engine using IntersectionObserver.
 * Zero dependencies. ~2KB unminified.
 */
(function () {
  'use strict';

  // ── Scroll Progress Bar ──────────────────────────────────────
  const progressBar = document.createElement('div');
  progressBar.className = 'scroll-progress';
  document.body.prepend(progressBar);

  function updateProgress() {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    if (docHeight > 0) {
      progressBar.style.width = ((scrollTop / docHeight) * 100) + '%';
    }
  }

  window.addEventListener('scroll', updateProgress, { passive: true });
  updateProgress();

  // ── Intersection Observer for Reveal Animations ──────────────
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (prefersReducedMotion) {
    // Immediately show everything if user prefers reduced motion
    document.querySelectorAll('.reveal').forEach(function (el) {
      el.classList.add('reveal-visible');
    });
    return;
  }

  const observerOptions = {
    root: null,
    rootMargin: '0px 0px -60px 0px', // Trigger slightly before full viewport entry
    threshold: 0.15
  };

  const revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('reveal-visible');
        revealObserver.unobserve(entry.target); // One-shot: don't re-trigger
      }
    });
  }, observerOptions);

  // Observe all elements with .reveal class
  document.querySelectorAll('.reveal').forEach(function (el) {
    revealObserver.observe(el);
  });

  // ── Hero Auto-Trigger ────────────────────────────────────────
  // Hero elements should animate immediately on page load (no scroll needed)
  window.addEventListener('load', function () {
    document.querySelectorAll('.hero-text-reveal, .hero-entrance').forEach(function (el) {
      // Small delay for the page to settle visually
      setTimeout(function () {
        el.classList.add('reveal-visible');
      }, 150);
    });
  });

})();
