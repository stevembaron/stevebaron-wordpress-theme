/* Steve Baron Theme — main.js */

(function () {
  'use strict';

  // ── Dark mode ──────────────────────────────────────────────────────────────

  var html = document.documentElement;

  function applyMode(mode) {
    html.setAttribute('data-mode', mode);
    localStorage.setItem('sb-mode', mode);
  }

  // Apply saved mode before paint (also set in header.php inline script)
  var savedMode = localStorage.getItem('sb-mode');
  if (savedMode) applyMode(savedMode);

  document.addEventListener('DOMContentLoaded', function () {

    // Toggle button
    var toggle = document.querySelector('.dark-toggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        var current = html.getAttribute('data-mode') || 'light';
        applyMode(current === 'dark' ? 'light' : 'dark');
      });
    }

    // ── Mobile nav ──────────────────────────────────────────────────────────

    var burger = document.querySelector('.nav-burger');
    var navLinks = document.querySelector('.nav-links');
    if (burger && navLinks) {
      burger.addEventListener('click', function () {
        var open = navLinks.classList.toggle('open');
        burger.setAttribute('aria-expanded', open);
        burger.innerHTML = open
          ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>'
          : '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>';
      });

      // Close on outside click
      document.addEventListener('click', function (e) {
        if (!e.target.closest('#site-header')) {
          navLinks.classList.remove('open');
          burger.setAttribute('aria-expanded', 'false');
        }
      });
    }

    // ── Topo SVG generation ─────────────────────────────────────────────────

    var topoSvg = document.getElementById('topo-bg');
    if (topoSvg) {
      generateTopo(topoSvg, 1.2);
    }

    // ── Blog/Photo category filter ──────────────────────────────────────────

    document.querySelectorAll('[data-filter-group]').forEach(function (group) {
      var chips = group.querySelectorAll('[data-filter]');
      var targetId = group.getAttribute('data-filter-group');
      var items = document.querySelectorAll('[data-filter-list="' + targetId + '"] [data-cat]');

      chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
          chips.forEach(function (c) { c.classList.remove('active'); });
          chip.classList.add('active');

          var cat = chip.getAttribute('data-filter');
          items.forEach(function (item) {
            if (cat === 'all' || item.getAttribute('data-cat') === cat) {
              item.style.display = '';
            } else {
              item.style.display = 'none';
            }
          });
        });
      });
    });

    // ── Photo lightbox (simple) ─────────────────────────────────────────────

    document.querySelectorAll('.photo-item').forEach(function (item) {
      item.addEventListener('click', function () {
        var img = item.querySelector('img');
        if (!img) return;
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:zoom-out;padding:20px;';
        var bigImg = document.createElement('img');
        bigImg.src = img.src;
        bigImg.srcset = img.srcset || '';
        bigImg.style.cssText = 'max-width:100%;max-height:90vh;object-fit:contain;border-radius:8px;';
        overlay.appendChild(bigImg);
        overlay.addEventListener('click', function () { document.body.removeChild(overlay); });
        document.addEventListener('keydown', function esc(e) { if (e.key === 'Escape') { document.body.removeChild(overlay); document.removeEventListener('keydown', esc); } });
        document.body.appendChild(overlay);
      });
    });

    // ── Scroll: nav shadow ──────────────────────────────────────────────────

    var header = document.getElementById('site-header');
    if (header) {
      window.addEventListener('scroll', function () {
        header.style.boxShadow = window.scrollY > 10
          ? '0 1px 16px rgba(0,0,0,.06)'
          : '';
      }, { passive: true });
    }

  }); // DOMContentLoaded

  // ── Topo SVG helper ────────────────────────────────────────────────────────

  function generateTopo(svg, seed) {
    var NS = 'http://www.w3.org/2000/svg';
    svg.innerHTML = '';
    for (var i = 0; i < 9; i++) {
      var r  = 60 + i * 40;
      var cx = 700 + Math.sin(seed + i * 0.7) * 40;
      var cy = 250 + Math.cos(seed + i * 0.5) * 30;
      var pts = [];
      var N = 40;
      for (var j = 0; j < N; j++) {
        var a = (j / N) * Math.PI * 2;
        var noise = 1 + Math.sin(a * 3 + i + seed) * 0.08 + Math.cos(a * 5 + i * 2) * 0.05;
        pts.push((cx + Math.cos(a) * r * noise).toFixed(1) + ',' + (cy + Math.sin(a) * r * noise * 0.8).toFixed(1));
      }
      var poly = document.createElementNS(NS, 'polygon');
      poly.setAttribute('points', pts.join(' '));
      poly.setAttribute('fill', 'none');
      poly.setAttribute('stroke', 'currentColor');
      poly.setAttribute('stroke-width', '0.8');
      svg.appendChild(poly);
    }
  }

})();
