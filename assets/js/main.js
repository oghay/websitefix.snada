/**
 * OJS Developer Indonesia – Main JavaScript
 * Handles: animations, navbar, portfolio filter, counter, form, back-to-top
 */

(function () {
  'use strict';

  // ──────────────────────────────────────────
  // 1. SCROLL ANIMATIONS (IntersectionObserver)
  // ──────────────────────────────────────────
  function initScrollAnimations() {
    const elements = document.querySelectorAll('.fade-in-up, .fade-in-right');
    if (!elements.length) return;

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    elements.forEach((el) => observer.observe(el));
  }

  // ──────────────────────────────────────────
  // 2. NAVBAR SCROLL BEHAVIOR
  // ──────────────────────────────────────────
  function initNavbarScroll() {
    const nav = document.getElementById('mainNav');
    if (!nav) return;

    let lastScrollY = window.scrollY;

    function onScroll() {
      const scrollY = window.scrollY;
      if (scrollY > 60) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
      lastScrollY = scrollY;
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // run once on load
  }

  // ──────────────────────────────────────────
  // 3. BACK TO TOP BUTTON
  // ──────────────────────────────────────────
  function initBackToTop() {
    const btn = document.getElementById('backToTop');
    if (!btn) return;

    // Show/hide on scroll
    window.addEventListener('scroll', () => {
      if (window.scrollY > 400) {
        btn.classList.add('visible');
      } else {
        btn.classList.remove('visible');
      }
    }, { passive: true });

    btn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ──────────────────────────────────────────
  // 4. STATS COUNTER ANIMATION
  // ──────────────────────────────────────────
  function initCounterAnimation() {
    const counters = document.querySelectorAll('.stat-number[data-count]');
    if (!counters.length) return;

    const easeOut = (t) => 1 - Math.pow(1 - t, 3);

    function animateCounter(el) {
      const target = parseInt(el.getAttribute('data-count'), 10);
      const duration = 1600; // ms
      const start = performance.now();

      function update(now) {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const value = Math.round(easeOut(progress) * target);
        el.textContent = value.toLocaleString('id-ID');
        if (progress < 1) {
          requestAnimationFrame(update);
        } else {
          el.textContent = target.toLocaleString('id-ID');
        }
      }

      requestAnimationFrame(update);
    }

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );

    counters.forEach((el) => observer.observe(el));
  }

  // ──────────────────────────────────────────
  // 5. PORTFOLIO FILTER (JavaScript version)
  //    Used on the portfolio page for instant
  //    client-side filtering if items are on one page.
  // ──────────────────────────────────────────
  function initPortfolioFilter() {
    const filterBtns = document.querySelectorAll('.btn-filter[data-filter]');
    const items = document.querySelectorAll('.portfolio-item[data-category]');
    if (!filterBtns.length || !items.length) return;

    filterBtns.forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const filter = btn.getAttribute('data-filter');

        // Update active state
        filterBtns.forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');

        // Filter items
        items.forEach((item) => {
          const cat = item.getAttribute('data-category');
          const show = filter === 'all' || cat === filter;
          item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
          if (show) {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
            item.style.display = '';
          } else {
            item.style.opacity = '0';
            item.style.transform = 'translateY(10px)';
            setTimeout(() => {
              if (item.style.opacity === '0') {
                item.style.display = 'none';
              }
            }, 300);
          }
        });
      });
    });
  }

  // ──────────────────────────────────────────
  // 6. SMOOTH SCROLL FOR ANCHOR LINKS
  // ──────────────────────────────────────────
  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach((link) => {
      link.addEventListener('click', (e) => {
        const href = link.getAttribute('href');
        if (href === '#' || href.length <= 1) return;
        const target = document.querySelector(href);
        if (!target) return;
        e.preventDefault();
        const navHeight = document.getElementById('mainNav')?.offsetHeight || 70;
        const top = target.getBoundingClientRect().top + window.scrollY - navHeight - 16;
        window.scrollTo({ top, behavior: 'smooth' });
      });
    });
  }

  // ──────────────────────────────────────────
  // 7. AUTO-DISMISS FLASH ALERTS
  // ──────────────────────────────────────────
  function initFlashAlerts() {
    const alerts = document.querySelectorAll('.flash-container .alert');
    alerts.forEach((alert) => {
      setTimeout(() => {
        const bsAlert = window.bootstrap?.Alert?.getOrCreateInstance(alert);
        if (bsAlert) {
          bsAlert.close();
        } else {
          alert.style.opacity = '0';
          alert.style.transition = 'opacity 0.5s ease';
          setTimeout(() => alert.remove(), 500);
        }
      }, 5000);
    });
  }

  // ──────────────────────────────────────────
  // 8. FORM VALIDATION HELPER
  //    Bootstrap 5 validation styles
  // ──────────────────────────────────────────
  function initFormValidation() {
    const forms = document.querySelectorAll('form[novalidate]');
    forms.forEach((form) => {
      // Real-time validation on input
      form.querySelectorAll('input, textarea, select').forEach((input) => {
        input.addEventListener('blur', () => {
          if (form.classList.contains('was-validated')) {
            input.checkValidity()
              ? input.classList.remove('is-invalid')
              : input.classList.add('is-invalid');
          }
        });
      });
    });
  }

  // ──────────────────────────────────────────
  // 9. HERO ILLUSTRATION PARALLAX (subtle)
  // ──────────────────────────────────────────
  function initHeroParallax() {
    const shapes = document.querySelectorAll('.hero-shape');
    if (!shapes.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    window.addEventListener('mousemove', (e) => {
      const xRatio = (e.clientX / window.innerWidth - 0.5) * 2;
      const yRatio = (e.clientY / window.innerHeight - 0.5) * 2;

      shapes.forEach((shape, i) => {
        const factor = (i + 1) * 6;
        shape.style.transform = `translate(${xRatio * factor}px, ${yRatio * factor}px)`;
      });
    }, { passive: true });
  }

  // ──────────────────────────────────────────
  // 10. ACTIVE NAV LINK DETECTION
  // ──────────────────────────────────────────
  function initActiveNav() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('#mainNav .nav-link').forEach((link) => {
      const linkPath = new URL(link.href, window.location.origin).pathname;
      if (
        linkPath === currentPath ||
        (linkPath !== '/' && currentPath.startsWith(linkPath))
      ) {
        link.classList.add('active');
      }
    });
  }

  // ──────────────────────────────────────────
  // 11. WHATSAPP FLOAT – Add class to body if present
  // ──────────────────────────────────────────
  function initWAFloat() {
    const waBtn = document.querySelector('.wa-float');
    if (waBtn) {
      document.body.classList.add('has-wa');
    }
  }

  // ──────────────────────────────────────────
  // INIT ALL
  // ──────────────────────────────────────────
  function init() {
    initScrollAnimations();
    initNavbarScroll();
    initBackToTop();
    initCounterAnimation();
    initPortfolioFilter();
    initSmoothScroll();
    initFlashAlerts();
    initFormValidation();
    initHeroParallax();
    initActiveNav();
    initWAFloat();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
