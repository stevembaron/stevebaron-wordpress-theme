/* Steve Baron Theme — main.js */

(function () {
  'use strict';

  var html = document.documentElement;
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ── Dark mode ──────────────────────────────────────────────────────────────

  function applyMode(mode) {
    html.setAttribute('data-mode', mode);
    try { localStorage.setItem('sb-mode', mode); } catch (e) {}
  }

  // (Initial mode is applied by the inline script in header.php.)

  // ── Lightbox state ─────────────────────────────────────────────────────────

  var lightboxOpen = false;
  var lastFocus = null;

  function openLightbox(img) {
    if (!img || lightboxOpen) return;
    lightboxOpen = true;
    lastFocus = document.activeElement;

    var overlay = document.createElement('div');
    overlay.className = 'sb-lightbox';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', img.alt || 'Photo');
    overlay.tabIndex = -1;

    var bigImg = document.createElement('img');
    bigImg.src = img.currentSrc || img.src;
    if (img.srcset) bigImg.srcset = img.srcset;
    bigImg.alt = img.alt || '';
    bigImg.decoding = 'async';
    overlay.appendChild(bigImg);

    function close() {
      if (!lightboxOpen) return;
      lightboxOpen = false;
      overlay.remove();
      document.body.style.overflow = '';
      document.removeEventListener('keydown', onKey);
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    function onKey(e) {
      if (e.key === 'Escape') close();
    }

    overlay.addEventListener('click', close);
    document.addEventListener('keydown', onKey);

    document.body.style.overflow = 'hidden';
    document.body.appendChild(overlay);
    overlay.focus();
  }

  document.addEventListener('DOMContentLoaded', function () {

    // ── Dark mode toggle ──────────────────────────────────────────────────

    var toggle = document.querySelector('.dark-toggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        var current = html.getAttribute('data-mode') || 'light';
        applyMode(current === 'dark' ? 'light' : 'dark');
      });
    }

    // ── Mobile nav ─────────────────────────────────────────────────────────

    var burger = document.querySelector('.nav-burger');
    var navLinks = document.querySelector('.nav-links');
    var BURGER_OPEN  = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>';
    var BURGER_CLOSE = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 12h18M3 6h18M3 18h18"/></svg>';

    if (burger && navLinks) {
      burger.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = navLinks.classList.toggle('open');
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        burger.innerHTML = open ? BURGER_OPEN : BURGER_CLOSE;
      });
      document.addEventListener('click', function (e) {
        if (!navLinks.classList.contains('open')) return;
        if (!e.target.closest('#site-header')) {
          navLinks.classList.remove('open');
          burger.setAttribute('aria-expanded', 'false');
          burger.innerHTML = BURGER_CLOSE;
        }
      });
    }

    // ── Topo SVG generation ────────────────────────────────────────────────

    var topoSvg = document.getElementById('topo-bg');
    if (topoSvg && !prefersReducedMotion) {
      generateTopo(topoSvg, 1.2);
    }

    // ── Filter chips (blog & photos) ───────────────────────────────────────

    document.querySelectorAll('[data-filter-group]').forEach(function (group) {
      var chips = group.querySelectorAll('[data-filter]');
      var targetId = group.getAttribute('data-filter-group');
      var items = document.querySelectorAll('[data-filter-list="' + targetId + '"] [data-cat]');
      if (!chips.length || !items.length) return;

      chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
          chips.forEach(function (c) { c.classList.remove('active'); });
          chip.classList.add('active');
          var cat = chip.getAttribute('data-filter');
          items.forEach(function (item) {
            item.style.display = (cat === 'all' || item.getAttribute('data-cat') === cat) ? '' : 'none';
          });
        });
      });
    });

    // ── Photo lightbox ─────────────────────────────────────────────────────

    document.querySelectorAll('.photo-item').forEach(function (item) {
      item.style.cursor = 'zoom-in';
      item.setAttribute('tabindex', '0');
      item.setAttribute('role', 'button');
      item.addEventListener('click', function () {
        openLightbox(item.querySelector('img'));
      });
      item.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openLightbox(item.querySelector('img'));
        }
      });
    });

    // ── Share button ───────────────────────────────────────────────────────

    document.querySelectorAll('.js-share').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var data = {
          title: btn.dataset.shareTitle || document.title,
          url:   btn.dataset.shareUrl   || location.href
        };
        if (navigator.share) {
          navigator.share(data).catch(function () { /* user dismissed */ });
        } else if (navigator.clipboard) {
          navigator.clipboard.writeText(data.url).then(function () {
            var orig = btn.textContent;
            btn.textContent = btn.dataset.copied || 'Copied!';
            setTimeout(function () { btn.textContent = orig; }, 1800);
          });
        } else {
          window.prompt('Copy this URL:', data.url);
        }
      });
    });

    // ── Sticky-nav shadow on scroll (toggled via class) ────────────────────

    var header = document.getElementById('site-header');
    if (header) {
      var scrolled = false;
      var update = function () {
        var should = window.scrollY > 10;
        if (should !== scrolled) {
          scrolled = should;
          header.classList.toggle('is-scrolled', scrolled);
        }
      };
      window.addEventListener('scroll', update, { passive: true });
      update();
    }
  });

  // ── Topo SVG helper ──────────────────────────────────────────────────────

  function generateTopo(svg, seed) {
    var NS = 'http://www.w3.org/2000/svg';
    var rings = 9;
    var pointsPerRing = 40;
    var frag = document.createDocumentFragment();

    for (var i = 0; i < rings; i++) {
      var r  = 60 + i * 40;
      var cx = 700 + Math.sin(seed + i * 0.7) * 40;
      var cy = 250 + Math.cos(seed + i * 0.5) * 30;
      var pts = [];
      for (var j = 0; j < pointsPerRing; j++) {
        var a = (j / pointsPerRing) * Math.PI * 2;
        var noise = 1 + Math.sin(a * 3 + i + seed) * 0.08 + Math.cos(a * 5 + i * 2) * 0.05;
        pts.push((cx + Math.cos(a) * r * noise).toFixed(1) + ',' + (cy + Math.sin(a) * r * noise * 0.8).toFixed(1));
      }
      var poly = document.createElementNS(NS, 'polygon');
      poly.setAttribute('points', pts.join(' '));
      poly.setAttribute('fill', 'none');
      poly.setAttribute('stroke', 'currentColor');
      poly.setAttribute('stroke-width', '0.8');
      frag.appendChild(poly);
    }
    svg.replaceChildren(frag);
  }

})();
