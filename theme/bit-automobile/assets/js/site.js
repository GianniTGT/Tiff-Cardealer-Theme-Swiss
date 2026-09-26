/* BIT Automobile — Verhalten wie im Entwurf:
   «Der Platz» (Zaehler + Bildwechsel beim Scrollen), Sofort-Suche auf
   /fahrzeuge/, Galerie und Leasing-Rechner auf der Fahrzeugseite. */
(function () {
  'use strict';

  var CH = function (n) {
    return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '’');
  };
  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Der Platz ---------- */
  var platz = document.querySelector('[data-platz]');
  if (platz) {
    var wipe = platz.querySelector('[data-wipe]');
    var edge = platz.querySelector('[data-edge]');
    var counter = platz.querySelector('[data-counter]');
    var total = parseInt(platz.getAttribute('data-count'), 10) || 0;
    var ticking = false;
    var update = function () {
      ticking = false;
      var r = platz.getBoundingClientRect();
      var span = r.height - window.innerHeight;
      var p = Math.max(0, Math.min(1, -r.top / (span || 1)));
      var a = Math.max(0, 100 - p * 165);
      var b = Math.max(0, 100 - p * 95);
      if (wipe) wipe.style.clipPath = 'polygon(0 0,100% 0,100% ' + a + '%,0 ' + b + '%)';
      if (edge) {
        edge.style.transform = 'translateY(' + b + 'vh) rotate(' + ((b - a) * 0.0055 * -57.3) + 'deg)';
        edge.style.opacity = p > 0.01 && p < 0.99 ? '0.9' : '0';
      }
      if (counter) {
        var n = Math.min(total, Math.round(p * 3.2 * total));
        if (counter.textContent !== String(n)) counter.textContent = String(n);
      }
    };
    if (!reduced) {
      if (counter) counter.textContent = '0';
      window.addEventListener('scroll', function () {
        if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
      }, { passive: true });
      window.addEventListener('resize', update);
      update();
    }
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

  /* ---------- Fahrzeug: Leasing-Rechner ---------- */
  var lease = document.querySelector('[data-leasing]');
  if (lease) {
    var price = +lease.getAttribute('data-price');
    var rate = (+lease.getAttribute('data-rate') || 4.9) / 100;
    var months = lease.querySelector('[data-in="months"]');
    var down = lease.querySelector('[data-in="down"]');
    var o = {};
    Array.prototype.forEach.call(lease.querySelectorAll('[data-out]'), function (el) { o[el.getAttribute('data-out')] = el; });
    var calc = function () {
      var m = +months.value, d = +down.value;
      var downChf = price * d / 100;
      var principal = price - downChf;
      var perMonth = principal * (1 + rate * m / 12) / m;
      o.months.textContent = m + ' Monate';
      o.down.textContent = d + '% · CHF ' + CH(downChf);
      o.downchf.textContent = '– CHF ' + CH(downChf);
      o.principal.textContent = 'CHF ' + CH(principal);
      o.rate.textContent = 'CHF ' + CH(perMonth);
    };
    months.addEventListener('input', calc);
    down.addEventListener('input', calc);
    calc();
  }
})();
