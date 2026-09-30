/* BIT Automobile — Verhalten wie im Entwurf:
   «Der Platz» (Zaehler + Bildwechsel beim Scrollen), Sofort-Suche auf
   /fahrzeuge/, Galerie auf der Fahrzeugseite. */
(function () {
  'use strict';

  var CH = function (n) {
    return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '’');
  };
  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Der Platz: Zaehler und Linie laufen beim Scrollen mit ---------- */
  var platz = document.querySelector('[data-platz]');
  if (platz && !reduced) {
    var counter = platz.querySelector('[data-counter]');
    var meter = platz.querySelector('[data-meter]');
    var wipe = platz.querySelector('[data-wipe]');
    var edge = platz.querySelector('[data-edge]');
    var total = parseInt(platz.getAttribute('data-count'), 10) || 0;
    var ticking = false;
    var update = function () {
      ticking = false;
      var r = platz.getBoundingClientRect();
      var span = r.height - window.innerHeight;
      var p = Math.max(0, Math.min(1, -r.top / (span || 1)));
      var n = Math.min(total, Math.round(p * 3.2 * total));
      if (counter && counter.textContent !== String(n)) counter.textContent = String(n);
      if (meter) meter.style.setProperty('--p', total ? String(n / total) : '1');
      /* Oberes Foto wischt schraeg weg: rechts schneller als links (Werte aus dem Entwurf) */
      var a = Math.max(0, 100 - p * 165);
      var b = Math.max(0, 100 - p * 95);
      if (wipe) wipe.style.clipPath = 'polygon(0 0,100% 0,100% ' + a + '%,0 ' + b + '%)';
      if (edge) {
        var st = platz.querySelector('.stick');
        var h = st.offsetHeight / 100;
        var w = st.offsetWidth || 1;
        var ang = Math.atan2((a - b) * h, w) * 180 / Math.PI;
        edge.style.transform = 'translateY(' + (b * h).toFixed(1) + 'px) rotate(' + ang.toFixed(2) + 'deg)';
        edge.style.opacity = p > 0.01 && p < 0.99 && b > 0 ? '0.9' : '0';
      }
    };
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
  }

  /* ---------- Zahlen zaehlen hoch, sobald sie sichtbar werden (echte Zahl steht schon im HTML) ---------- */
  var ups = document.querySelectorAll('[data-countup]');
  if (ups.length && !reduced && 'IntersectionObserver' in window) {
    var run = function (el) {
      var to = parseInt(el.getAttribute('data-countup'), 10) || 0;
      var t0 = null;
      var step = function (t) {
        if (t0 === null) t0 = t;
        var k = Math.min(1, (t - t0) / 1100);
        el.textContent = CH(to * (1 - Math.pow(1 - k, 3)));
        if (k < 1) window.requestAnimationFrame(step);
      };
      el.textContent = '0';
      window.requestAnimationFrame(step);
    };
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { io.unobserve(e.target); run(e.target); } });
    }, { threshold: 0.6 });
    Array.prototype.forEach.call(ups, function (el) { io.observe(el); });
  }

  /* ---------- Fahrzeuge: Sofort-Suche ---------- */
  var finder = document.querySelector('[data-finder]');
  if (finder) {
    var grid = finder.querySelector('[data-grid]');
    var cards = grid ? Array.prototype.slice.call(grid.querySelectorAll('.bit-veh')) : [];
    var q = finder.querySelector('[data-q]');
    var chips = Array.prototype.slice.call(finder.querySelectorAll('.bit-chip[data-brand]'));
    var ranges = {};
    Array.prototype.forEach.call(finder.querySelectorAll('[data-range]'), function (el) { ranges[el.getAttribute('data-range')] = el; });
    var outs = {};
    Array.prototype.forEach.call(finder.querySelectorAll('[data-out]'), function (el) { outs[el.getAttribute('data-out')] = el; });
    var label = finder.querySelector('[data-count-label]');
    var empty = finder.querySelector('[data-empty]');
    var totalCars = cards.length;
    var brand = '';
    chips.forEach(function (c) { if (c.getAttribute('aria-pressed') === 'true') brand = c.getAttribute('data-brand'); });

    var apply = function () {
      var maxP = ranges.price ? +ranges.price.value : Infinity;
      var maxK = ranges.km ? +ranges.km.value : Infinity;
      var minY = ranges.year ? +ranges.year.value : 0;
      var noP = !ranges.price || maxP >= +ranges.price.max;
      var noK = !ranges.km || maxK >= +ranges.km.max;
      if (outs.price) outs.price.textContent = noP ? 'ohne Grenze' : 'CHF ' + CH(maxP);
      if (outs.km) outs.km.textContent = noK ? 'ohne Grenze' : CH(maxK) + ' km';
      if (outs.year) outs.year.textContent = String(minY);
      var terms = (q && q.value ? q.value : '').trim().toLowerCase().split(/\s+/).filter(Boolean);
      var hits = 0;
      cards.forEach(function (card) {
        var d = card.dataset;
        var ok = true;
        if (brand && d.brand !== brand) ok = false;
        if (ok && !noP && +d.price > maxP) ok = false;
        if (ok && !noK && +d.km > maxK) ok = false;
        if (ok && +d.year && +d.year < minY) ok = false;
        if (ok && terms.length) ok = terms.every(function (w) { return d.hay.indexOf(w) !== -1; });
        card.hidden = !ok;
        if (ok) hits++;
      });
      if (label) label.textContent = hits === totalCars
        ? totalCars + (totalCars === 1 ? ' Fahrzeug auf dem Platz' : ' Fahrzeuge auf dem Platz')
        : hits + ' von ' + totalCars + ' Fahrzeugen';
      if (empty) empty.hidden = hits !== 0 || totalCars === 0;
    };

    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        brand = chip.getAttribute('data-brand');
        chips.forEach(function (c) { c.setAttribute('aria-pressed', c === chip ? 'true' : 'false'); });
        apply();
      });
    });
    if (q) q.addEventListener('input', apply);
    Object.keys(ranges).forEach(function (k) { ranges[k].addEventListener('input', apply); });
    Array.prototype.forEach.call(finder.querySelectorAll('[data-reset]'), function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        if (q) q.value = '';
        if (ranges.price) ranges.price.value = ranges.price.max;
        if (ranges.km) ranges.km.value = ranges.km.max;
        if (ranges.year) ranges.year.value = ranges.year.min;
        brand = '';
        chips.forEach(function (c) { c.setAttribute('aria-pressed', c.getAttribute('data-brand') === '' ? 'true' : 'false'); });
        apply();
      });
    });
    apply();
  }

  /* ---------- Fahrzeug: Galerie ---------- */
  var gal = document.querySelector('[data-gallery]');
  if (gal) {
    var main = gal.querySelector('[data-main]');
    var thumbs = Array.prototype.slice.call(gal.querySelectorAll('.thumbs button'));
    thumbs.forEach(function (t) {
      t.addEventListener('click', function () {
        if (main) { main.removeAttribute('srcset'); main.src = t.getAttribute('data-full'); }
        thumbs.forEach(function (o) { o.setAttribute('aria-pressed', o === t ? 'true' : 'false'); });
      });
    });
  }

  /* =================================================================
     Bewegung (nur mit html.js-anim, also ohne «Bewegung reduzieren")
     ================================================================= */
  var anim = document.documentElement.classList.contains('js-anim');

  /* Linie oben beim Seitenwechsel */
  var line = document.createElement('div');
  line.className = 'navline';
  line.setAttribute('aria-hidden', 'true');
  document.body.appendChild(line);
  var go = function () { line.classList.remove('go'); void line.offsetWidth; line.classList.add('go'); };
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href]');
    if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (a.target && a.target !== '_self') return;
    var url;
    try { url = new URL(a.href, location.href); } catch (err) { return; }
    if (url.origin !== location.origin) return;
    if (url.pathname === location.pathname && url.search === location.search && url.hash) return;
    go();
  });
  document.addEventListener('submit', go);
  window.addEventListener('pageshow', function () { line.classList.remove('go'); });

  /* Bloecke steigen beim Scrollen auf, gestaffelt */
  if (anim) {
    var sel = '.intro > *, .hero .in > *, .sec-head, .bit-facts, .grid-veh > .bit-veh, .svc-grid > .svc, .banner, ' +
      '.split > *, .criteria > div, .legal > *, .panel, .vcard, .find, .chips, .ranges, .detail > *, .prose > *, .platz .in > *, .sitefoot .cols > div';
    var items = Array.prototype.slice.call(document.querySelectorAll(sel));
    items.forEach(function (el) {
      var i = Array.prototype.indexOf.call(el.parentNode.children, el);
      el.setAttribute('data-rv', '');
      el.style.setProperty('--d', Math.min(i, 5) * 70 + 'ms');
    });
    if ('IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
        });
      }, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 });
      items.forEach(function (el) { io.observe(el); });
    } else {
      items.forEach(function (el) { el.classList.add('is-in'); });
    }
  }

  /* Maus: das «bit» aus dem Logo zieht hinter dem Zeiger her */
  if (anim && window.BIT && window.BIT.mark && window.matchMedia('(pointer: fine)').matches) {
    var mark = document.createElement('span');
    mark.className = 'bit-follow';
    mark.style.setProperty('--mark', 'url("' + window.BIT.mark + '")');
    mark.setAttribute('aria-hidden', 'true');
    document.body.appendChild(mark);
    var tx = -200, ty = -200, x = -200, y = -200, last = 0, shown = false;
    window.addEventListener('mousemove', function (e) { tx = e.clientX + 14; ty = e.clientY + 18; last = performance.now(); }, { passive: true });
    document.addEventListener('mouseleave', function () { last = 0; });
    var loop = function () {
      x += (tx - x) * 0.085;
      y += (ty - y) * 0.085;
      mark.style.transform = 'translate3d(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px,0)';
      var on = performance.now() - last < 700;
      if (on !== shown) { shown = on; mark.classList.toggle('on', on); }
      window.requestAnimationFrame(loop);
    };
    window.requestAnimationFrame(loop);
  }
})();
