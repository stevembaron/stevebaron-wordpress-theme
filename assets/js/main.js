/* Steve Baron Theme — main.js */

(function () {
  'use strict';

  var html = document.documentElement;
  var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var SB = window.SB || {};
  var HOME = (SB.home || (location.origin + '/')).replace(/\/?$/, '/');

  // ── Dark mode ────────────────────────────────────────────────────────────

  function applyMode(mode) {
    html.setAttribute('data-mode', mode);
    try { localStorage.setItem('sb-mode', mode); } catch (e) {}
  }

  // ── Lightbox state ───────────────────────────────────────────────────────

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
    function onKey(e) { if (e.key === 'Escape') close(); }

    overlay.addEventListener('click', close);
    document.addEventListener('keydown', onKey);
    document.body.style.overflow = 'hidden';
    document.body.appendChild(overlay);
    overlay.focus();
  }

  // ── Live SLC weather (NWS API) ───────────────────────────────────────────

  function forecastEmoji(s) {
    s = (s || '').toLowerCase();
    if (s.indexOf('thunder') !== -1) return '⛈';
    if (s.indexOf('snow') !== -1 || s.indexOf('flurries') !== -1 || s.indexOf('blizzard') !== -1) return '❄';
    if (s.indexOf('sleet') !== -1 || s.indexOf('freezing') !== -1) return '🌨';
    if (s.indexOf('rain') !== -1 || s.indexOf('shower') !== -1 || s.indexOf('drizzle') !== -1) return '🌧';
    if (s.indexOf('fog') !== -1 || s.indexOf('haze') !== -1 || s.indexOf('smoke') !== -1) return '🌫';
    if (s.indexOf('partly') !== -1) return '⛅';
    if (s.indexOf('mostly cloudy') !== -1 || (s.indexOf('cloudy') !== -1 && s.indexOf('mostly sunny') === -1)) return '☁';
    if (s.indexOf('mostly') !== -1) return '🌤';
    if (s.indexOf('sunny') !== -1 || s.indexOf('clear') !== -1) return '☀';
    if (s.indexOf('wind') !== -1) return '💨';
    return '🌡';
  }

  function loadLiveWeather() {
    var el = document.querySelector('.hero-weather');
    if (!el) return;
    var fallback = el.textContent;

    function show(text) {
      el.textContent = text;
      el.classList.add('is-live');
      el.title = 'Live from the National Weather Service · Salt Lake City';
    }

    // 30-min sessionStorage cache
    try {
      var cached = JSON.parse(sessionStorage.getItem('sb-wx') || 'null');
      if (cached && cached.t && Date.now() - cached.t < 30 * 60 * 1000 && cached.text) {
        show(cached.text);
        return;
      }
    } catch (e) {}

    // SLC gridpoint, hardcoded to skip the points lookup hop.
    fetch('https://api.weather.gov/gridpoints/SLC/97,176/forecast', { headers: { 'Accept': 'application/geo+json' } })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
      .then(function (data) {
        var p = data && data.properties && data.properties.periods && data.properties.periods[0];
        if (!p) return;
        var brief = (p.shortForecast || '').toLowerCase();
        var text = forecastEmoji(p.shortForecast) + ' ' + p.temperature + '°' + (p.temperatureUnit || 'F') + ' · ' + brief + ' over the Wasatch';
        show(text);
        try { sessionStorage.setItem('sb-wx', JSON.stringify({ t: Date.now(), text: text })); } catch (e) {}
      })
      .catch(function () { /* keep fallback text */ });
  }

  // ── Reading progress bar (post pages only) ───────────────────────────────

  function initReadingProgress() {
    var article = document.querySelector('.post-hero .entry-content');
    if (!article) return;
    var bar = document.createElement('div');
    bar.className = 'sb-read-progress';
    bar.setAttribute('role', 'progressbar');
    bar.setAttribute('aria-hidden', 'true');
    document.body.appendChild(bar);

    function update() {
      var rect = article.getBoundingClientRect();
      var visible = window.innerHeight - rect.top;
      var total   = rect.height + window.innerHeight * 0.3;
      var p = Math.min(1, Math.max(0, visible / total));
      bar.style.transform = 'scaleX(' + p.toFixed(3) + ')';
    }
    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
  }

  // ── Stats counter animation ──────────────────────────────────────────────

  function initStatsCounters() {
    if (prefersReducedMotion) return;
    var nums = document.querySelectorAll('.stat-num');
    if (!nums.length || !('IntersectionObserver' in window)) return;

    function animate(el) {
      var raw = el.textContent.trim();
      var match = raw.match(/^([^\d-]*)(-?\d+(?:\.\d+)?)([^\d.].*)?$/);
      if (!match) return; // skip e.g. "#1" (no leading digit)
      var prefix = match[1] || '';
      var target = parseFloat(match[2]);
      var suffix = match[3] || '';
      var isInt = match[2].indexOf('.') === -1;
      if (target === 0) return;
      var dur = 1400;
      var t0  = performance.now();
      el.dataset.target = raw;

      function frame(t) {
        var p = Math.min(1, (t - t0) / dur);
        var eased = 1 - Math.pow(1 - p, 3);
        var val = target * eased;
        el.textContent = prefix + (isInt ? Math.round(val) : val.toFixed(1)) + suffix;
        if (p < 1) requestAnimationFrame(frame);
        else el.textContent = raw;
      }
      el.textContent = prefix + 0 + suffix;
      requestAnimationFrame(frame);
    }

    var seen = new WeakSet();
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting && !seen.has(e.target)) {
          seen.add(e.target);
          animate(e.target);
        }
      });
    }, { threshold: 0.5 });
    nums.forEach(function (n) { io.observe(n); });
  }

  // ── Heading anchors in post content ──────────────────────────────────────

  function initHeadingAnchors() {
    var entry = document.querySelector('.post-hero .entry-content, .entry-content');
    if (!entry) return;
    var hs = entry.querySelectorAll('h2, h3');
    var used = {};
    hs.forEach(function (h) {
      if (!h.id) {
        var base = (h.textContent || '').trim().toLowerCase()
          .replace(/[^a-z0-9\s-]/g, '')
          .replace(/\s+/g, '-')
          .replace(/-+/g, '-')
          .slice(0, 60) || 'section';
        var id = base; var n = 2;
        while (used[id] || document.getElementById(id)) { id = base + '-' + (n++); }
        used[id] = true;
        h.id = id;
      }
      if (h.querySelector('.sb-anchor')) return;
      var a = document.createElement('a');
      a.className = 'sb-anchor';
      a.href = '#' + h.id;
      a.setAttribute('aria-label', 'Permalink to this heading');
      a.textContent = '#';
      a.addEventListener('click', function (e) {
        e.preventDefault();
        var url = location.origin + location.pathname + '#' + h.id;
        history.replaceState(null, '', '#' + h.id);
        h.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
        if (navigator.clipboard) {
          navigator.clipboard.writeText(url).then(function () {
            a.classList.add('is-copied');
            setTimeout(function () { a.classList.remove('is-copied'); }, 1400);
          });
        }
      });
      h.appendChild(a);
    });
  }

  // ── Topo SVG mouse parallax ──────────────────────────────────────────────

  function initTopoParallax() {
    if (prefersReducedMotion) return;
    var svg = document.getElementById('topo-bg');
    if (!svg) return;
    var rafId = 0; var tx = 0; var ty = 0;
    document.addEventListener('mousemove', function (e) {
      tx = (e.clientX / window.innerWidth  - 0.5) * 36;
      ty = (e.clientY / window.innerHeight - 0.5) * 24;
      if (rafId) return;
      rafId = requestAnimationFrame(function () {
        svg.style.transform = 'translate3d(' + tx + 'px,' + ty + 'px,0)';
        rafId = 0;
      });
    }, { passive: true });
  }

  // ── Snow easter egg (winter or 5x hero-weather click) ────────────────────

  var snowed = false;

  function runSnow(durationMs) {
    if (snowed || prefersReducedMotion) return;
    snowed = true;
    var canvas = document.createElement('canvas');
    canvas.className = 'sb-snow';
    function size() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
    size();
    window.addEventListener('resize', size, { passive: true });
    document.body.appendChild(canvas);
    var ctx = canvas.getContext('2d');
    var N = Math.min(120, Math.floor(window.innerWidth / 14));
    var flakes = [];
    for (var i = 0; i < N; i++) {
      flakes.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height - canvas.height,
        r: Math.random() * 2.2 + 0.6,
        d: Math.random() * 1.4 + 0.4,
        drift: (Math.random() - 0.5) * 0.6,
      });
    }
    var stop = Date.now() + durationMs;
    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = 'rgba(255,255,255,0.85)';
      for (var i = 0; i < flakes.length; i++) {
        var f = flakes[i];
        ctx.beginPath();
        ctx.arc(f.x, f.y, f.r, 0, Math.PI * 2);
        ctx.fill();
        f.y += f.d;
        f.x += f.drift;
        if (f.y - f.r > canvas.height) { f.y = -5; f.x = Math.random() * canvas.width; }
      }
      if (Date.now() < stop) requestAnimationFrame(draw);
      else {
        canvas.style.transition = 'opacity .8s';
        canvas.style.opacity = '0';
        setTimeout(function () { canvas.remove(); snowed = false; }, 900);
      }
    }
    draw();
  }

  function maybeSnowfall() {
    if (prefersReducedMotion) return;
    // Click-the-weather-5x easter egg, regardless of season
    var weather = document.querySelector('.hero-weather');
    if (weather) {
      var clicks = 0; var first = 0;
      weather.addEventListener('click', function () {
        var now = Date.now();
        if (now - first > 3000) { clicks = 0; first = now; }
        clicks++;
        if (clicks >= 5) { clicks = 0; runSnow(7000); }
      });
    }
    // Auto: winter months, once per session, only on the home page
    var month = new Date().getMonth(); // 0=Jan
    var isWinter = month === 11 || month <= 1;
    var isHome   = document.querySelector('.hero') !== null;
    if (!isWinter || !isHome) return;
    try { if (sessionStorage.getItem('sb-snowed')) return; } catch (e) {}
    setTimeout(function () {
      runSnow(7000);
      try { sessionStorage.setItem('sb-snowed', '1'); } catch (e) {}
    }, 2400);
  }

  // ── Footer weather-term rotator ──────────────────────────────────────────

  function initWxRotator() {
    var el = document.querySelector('.footer-altitude .wx-rotate');
    if (!el || prefersReducedMotion) return;
    var terms = [
      'barometric pressure',
      'dew point',
      'wet bulb temperature',
      'wind chill',
      'mean sea-level pressure',
      'frost point',
      'METAR observations',
      'lapse rate',
      'isobars',
      'orographic lift',
    ];
    var i = 0;
    setInterval(function () {
      el.style.opacity = '0';
      setTimeout(function () {
        i = (i + 1) % terms.length;
        el.textContent = terms[i];
        el.style.opacity = '';
      }, 350);
    }, 4200);
  }

  // ── Cmd/Ctrl + K command palette ─────────────────────────────────────────

  function initCommandPalette() {
    var overlay = null, input = null, list = null;
    var items = [], activeIndex = 0;

    function darkToggleAction() {
      var cur = html.getAttribute('data-mode') === 'dark' ? 'light' : 'dark';
      applyMode(cur);
    }

    // Static commands (pages + actions)
    var staticCommands = [
      { name: 'Go: Home',     url: HOME,                 type: 'page' },
      { name: 'Go: About',    url: HOME + 'about/',      type: 'page' },
      { name: 'Go: CV',       url: HOME + 'cv/',         type: 'page' },
      { name: 'Go: Projects', url: HOME + 'projects/',   type: 'page' },
      { name: 'Go: Writing',  url: HOME + 'writing/',    type: 'page' },
      { name: 'Go: Photos',   url: HOME + 'photos/',     type: 'page' },
      { name: 'Go: Now',      url: HOME + 'now/',        type: 'page' },
      { name: 'Go: Contact',  url: HOME + 'contact/',    type: 'page' },
      { name: 'Toggle dark mode',         type: 'action', action: darkToggleAction },
      { name: 'Subscribe (RSS feed)',     url: HOME + 'feed/',                                  type: 'external' },
      { name: 'View source on GitHub',    url: 'https://github.com/stevembaron/stevebaron-wordpress-theme', type: 'external' },
    ];

    var posts = [];
    // Lazy: load posts on first open to keep page load fast.
    var postsLoaded = false;
    function loadPosts() {
      if (postsLoaded) return;
      postsLoaded = true;
      fetch(HOME + 'wp-json/wp/v2/posts?per_page=30&_fields=id,title,link', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.ok ? r.json() : []; })
        .then(function (data) {
          posts = (data || []).map(function (p) {
            return { name: p.title.rendered.replace(/<[^>]+>/g, ''), url: p.link, type: 'post' };
          });
          if (overlay && overlay.classList.contains('is-open')) render();
        })
        .catch(function () {});
    }

    function escapeHtml(s) {
      return (s + '').replace(/[&<>"]/g, function (m) { return { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[m]; });
    }

    function badgeFor(type) {
      if (type === 'post')     return 'post';
      if (type === 'external') return 'external ↗';
      if (type === 'action')   return 'action';
      return 'page';
    }

    function render() {
      var q = input.value.trim().toLowerCase();
      var all = staticCommands.concat(posts);
      if (q) {
        items = all.filter(function (c) { return c.name.toLowerCase().indexOf(q) !== -1; });
      } else {
        items = all;
      }
      activeIndex = 0;
      list.innerHTML = items.length
        ? items.map(function (c, i) {
            return '<div class="sb-cmdk-item' + (i === 0 ? ' is-active' : '') + '" role="option" data-i="' + i + '">'
              + '<span class="sb-cmdk-name">' + escapeHtml(c.name) + '</span>'
              + '<span class="sb-cmdk-badge">' + badgeFor(c.type) + '</span>'
              + '</div>';
          }).join('')
        : '<div class="sb-cmdk-empty">No matches</div>';
      Array.prototype.forEach.call(list.children, function (el) {
        if (!el.dataset || !el.dataset.i) return;
        el.addEventListener('click', function () { execute(parseInt(el.dataset.i, 10)); });
        el.addEventListener('mouseenter', function () { setActive(parseInt(el.dataset.i, 10), false); });
      });
    }

    function setActive(i, scroll) {
      if (!items.length) return;
      activeIndex = Math.max(0, Math.min(items.length - 1, i));
      Array.prototype.forEach.call(list.children, function (el, idx) {
        el.classList && el.classList.toggle('is-active', idx === activeIndex);
      });
      if (scroll !== false) {
        var a = list.children[activeIndex];
        if (a && a.scrollIntoView) a.scrollIntoView({ block: 'nearest' });
      }
    }

    function execute(i) {
      var c = items[i]; if (!c) return;
      if (c.action) { c.action(); close(); return; }
      if (c.url) {
        if (c.type === 'external') window.open(c.url, '_blank', 'noopener');
        else location.href = c.url;
      }
      close();
    }

    function onKey(e) {
      if (e.key === 'Escape') { e.preventDefault(); close(); }
      else if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIndex + 1); }
      else if (e.key === 'ArrowUp')   { e.preventDefault(); setActive(activeIndex - 1); }
      else if (e.key === 'Enter')     { e.preventDefault(); execute(activeIndex); }
    }

    function build() {
      overlay = document.createElement('div');
      overlay.className = 'sb-cmdk';
      overlay.innerHTML =
        '<div class="sb-cmdk-panel" role="dialog" aria-modal="true" aria-label="Command palette">' +
          '<input type="text" class="sb-cmdk-input" placeholder="Search pages, posts, commands…" autocomplete="off" spellcheck="false">' +
          '<div class="sb-cmdk-list" role="listbox"></div>' +
          '<div class="sb-cmdk-hint">' +
            '<span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>' +
            '<span><kbd>↵</kbd> select</span>' +
            '<span><kbd>esc</kbd> close</span>' +
          '</div>' +
        '</div>';
      input = overlay.querySelector('.sb-cmdk-input');
      list  = overlay.querySelector('.sb-cmdk-list');
      overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
      input.addEventListener('input', render);
      input.addEventListener('keydown', onKey);
      document.body.appendChild(overlay);
    }

    function open() {
      if (!overlay) build();
      loadPosts();
      overlay.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      input.value = '';
      render();
      setTimeout(function () { input.focus(); }, 10);
    }

    function close() {
      if (!overlay) return;
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
      var typing = /^(input|textarea|select)$/i.test((e.target.tagName || '')) || e.target.isContentEditable;
      if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
        e.preventDefault();
        if (overlay && overlay.classList.contains('is-open')) close();
        else open();
      } else if (e.key === '/' && !typing && !e.metaKey && !e.ctrlKey && !e.altKey) {
        e.preventDefault();
        open();
      }
    });
  }

  // ── Boot ─────────────────────────────────────────────────────────────────

  document.addEventListener('DOMContentLoaded', function () {

    var toggle = document.querySelector('.dark-toggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        var current = html.getAttribute('data-mode') || 'light';
        applyMode(current === 'dark' ? 'light' : 'dark');
      });
    }

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

    var topoSvg = document.getElementById('topo-bg');
    if (topoSvg && !prefersReducedMotion) generateTopo(topoSvg, 1.2);

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

    document.querySelectorAll('.photo-item').forEach(function (item) {
      item.style.cursor = 'zoom-in';
      item.setAttribute('tabindex', '0');
      item.setAttribute('role', 'button');
      item.addEventListener('click', function () { openLightbox(item.querySelector('img')); });
      item.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openLightbox(item.querySelector('img')); }
      });
    });

    document.querySelectorAll('.js-share').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var data = { title: btn.dataset.shareTitle || document.title, url: btn.dataset.shareUrl || location.href };
        if (navigator.share) {
          navigator.share(data).catch(function () {});
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

    // Sticky-nav shadow on scroll
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

    // New goodies
    loadLiveWeather();
    initReadingProgress();
    initStatsCounters();
    initHeadingAnchors();
    initTopoParallax();
    initCommandPalette();
    initWxRotator();
    maybeSnowfall();
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
