/* DAS V4 - front-end behaviour. Data comes from wp_localize_script('das-v4', 'DAS', ...) */
(function () {
	'use strict';

	var DATA = window.DAS || {};
	var cars = Array.isArray(DATA.cars) ? DATA.cars : [];

	function $(sel, ctx) { return (ctx || document).querySelector(sel); }
	function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
	function money(n) {
		return '$' + Math.round(n).toLocaleString('en-US');
	}

	/* ---------- mobile menu ---------- */
	var hamb = $('.hamb');
	var menu = $('#menu');
	if (hamb && menu) {
		hamb.addEventListener('click', function () {
			var open = menu.classList.toggle('open');
			hamb.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		$$('#menu a').forEach(function (a) {
			a.addEventListener('click', function () {
				menu.classList.remove('open');
				hamb.setAttribute('aria-expanded', 'false');
			});
		});
	}

	/* ---------- hero showcase ---------- */
	var showcase = $('#showcase');
	if (showcase) {
		var featured = cars.filter(function (c) { return c.feat; });
		if (!featured.length) { featured = cars.slice(0, 4); }

		if (featured.length) {
			var idx = 0;
			var timer = null;
			var dots = $('#scDots');
			var body = $('#scBody');
			var stage = $('#scStage');

			featured.forEach(function (_, i) {
				var d = document.createElement('button');
				d.type = 'button';
				d.className = 'sc-dot' + (i === 0 ? ' on' : '');
				d.setAttribute('aria-label', 'Show featured vehicle ' + (i + 1));
				d.addEventListener('click', function () { show(i); restart(); });
				if (dots) { dots.appendChild(d); }
			});

			function show(i) {
				idx = i;
				var c = featured[i];
				if (body) {
					body.classList.remove('fadeSwap');
					void body.offsetWidth;
					body.classList.add('fadeSwap');
				}
				var name = $('#scName');
				var price = $('#scPrice');
				var count = $('#scCount');
				var chips = $('#scChips');
				var img = $('#scImg');

				if (name) {
					name.textContent = c.name;
					if (c.url) {
						name.innerHTML = '';
						var a = document.createElement('a');
						a.href = c.url;
						a.textContent = c.name;
						name.appendChild(a);
					}
				}
				if (price) { price.textContent = c.price ? money(c.price) : 'Call for price'; }
				if (count) { count.textContent = (i + 1) + ' / ' + featured.length; }
				if (chips) {
					chips.innerHTML = '';
					[c.eng, c.fuel, c.mi, 'Anchorage, AK'].forEach(function (t) {
						if (!t || t === '\u2014') { return; }
						var s = document.createElement('span');
						s.className = 'sc-chip';
						s.textContent = t;
						chips.appendChild(s);
					});
				}
				if (img && c.img) { img.src = c.img; img.alt = c.name; }
				else if (stage && c.img && !img) {
					var fresh = document.createElement('img');
					fresh.id = 'scImg';
					fresh.src = c.img;
					fresh.alt = c.name;
					var svg = $('svg', stage);
					if (svg) { stage.replaceChild(fresh, svg); }
				}
				$$('.sc-dot').forEach(function (d, j) { d.classList.toggle('on', j === i); });
			}

			function restart() {
				clearInterval(timer);
				if (featured.length > 1) {
					timer = setInterval(function () { show((idx + 1) % featured.length); }, 4500);
				}
			}

			showcase.addEventListener('mouseenter', function () { clearInterval(timer); });
			showcase.addEventListener('mouseleave', restart);

			show(0);
			restart();
		}
	}

	/* ---------- inventory filters (cards are rendered server-side) ---------- */
	var grid = $('#carGrid');
	if (grid) {
		var empty = null;

		function applyFilter(f) {
			var shown = 0;
			$$('.card', grid).forEach(function (card) {
				var types = (card.getAttribute('data-type') || '').split(/\s+/);
				var match = (f === 'all') || types.indexOf(f) !== -1;
				card.style.display = match ? '' : 'none';
				if (match) { shown++; }
			});

			if (!shown) {
				if (!empty) {
					empty = document.createElement('p');
					empty.className = 'grid-empty';
					empty.textContent = 'No vehicles in this category right now \u2014 call us, new inventory arrives weekly.';
					grid.appendChild(empty);
				}
				empty.style.display = '';
			} else if (empty) {
				empty.style.display = 'none';
			}
		}

		$$('.chip').forEach(function (chip) {
			chip.addEventListener('click', function () {
				$$('.chip').forEach(function (c) { c.classList.remove('active'); });
				chip.classList.add('active');
				applyFilter(chip.getAttribute('data-f'));
			});
		});

		$$('[data-jump]').forEach(function (a) {
			a.addEventListener('click', function () {
				var chip = document.querySelector('.chip[data-f="' + a.getAttribute('data-jump') + '"]');
				if (chip) { setTimeout(function () { chip.click(); }, 60); }
			});
		});
	}

	/* ---------- financing calculator ---------- */
	var finCar = $('#finCar');
	if (finCar && cars.length) {
		cars.forEach(function (c, i) {
			finCar.add(new Option(c.name + (c.price ? ' \u2014 ' + money(c.price) : ''), String(i)));
		});

		function calc() {
			var c = cars[parseInt(finCar.value, 10) || 0];
			if (!c) { return; }
			var down = Math.max(0, parseFloat($('#finDown').value) || 0);
			var n = parseInt($('#finTerm').value, 10);
			var apr = Math.max(0, parseFloat($('#finApr').value) || 0);
			var P = Math.max(0, (c.price || 0) - down);
			var r = apr / 100 / 12;
			var m = r > 0 ? P * r / (1 - Math.pow(1 + r, -n)) : P / n;
			if (!isFinite(m)) { m = 0; }

			$('#frMonthly').textContent = money(m);
			$('#frCarName').textContent = c.name + ' \u00b7 ' + n + ' months @ ' + apr + '% APR';
			$('#frPrice').textContent = money(c.price || 0);
			$('#frDown').textContent = money(down);
			$('#frLoan').textContent = money(P);
			$('#frTotal').textContent = money(m * n + down);
		}

		['finCar', 'finDown', 'finTerm', 'finApr'].forEach(function (id) {
			var el = document.getElementById(id);
			if (el) { el.addEventListener('input', calc); el.addEventListener('change', calc); }
		});
		calc();
	}

	/* ---------- single vehicle gallery ---------- */
	/* The swiping itself is the browser's: the stage is a scroll container with
	   scroll-snap. Everything here only follows it — which is why the gallery still
	   works with JavaScript off, and why nothing is bound to a touch event. */
	var stage = $('#carStage');
	if (stage && stage.classList.contains('is-slider')) {
		var thumbs = $('#carThumbs');
		var prevBtn = $('#galPrev'), nextBtn = $('#galNext'), counter = $('#galCount');
		var shots = stage.children.length;
		var at = function () {
			return stage.clientWidth ? Math.round(stage.scrollLeft / stage.clientWidth) : 0;
		};
		var go = function (i) {
			i = Math.max(0, Math.min(shots - 1, i));
			stage.scrollTo({ left: i * stage.clientWidth, behavior: 'smooth' });
		};
		var sync = function () {
			var i = at();
			if (counter) { counter.textContent = (i + 1) + ' / ' + shots; }
			if (prevBtn) { prevBtn.hidden = i <= 0; }
			if (nextBtn) { nextBtn.hidden = i >= shots - 1; }
			if (thumbs) {
				$$('button', thumbs).forEach(function (b, j) { b.classList.toggle('on', j === i); });
			}
		};
		/* Settled, not every frame: a swipe fires scroll dozens of times and the counter
		   should read the photo it lands on, not the ones it passed. */
		var timer;
		stage.addEventListener('scroll', function () {
			clearTimeout(timer);
			timer = setTimeout(sync, 60);
		});
		if (prevBtn) { prevBtn.addEventListener('click', function () { go(at() - 1); }); }
		if (nextBtn) { nextBtn.addEventListener('click', function () { go(at() + 1); }); }
		if (thumbs) {
			$$('button', thumbs).forEach(function (b, j) {
				b.addEventListener('click', function () { go(j); });
			});
		}
		stage.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { e.preventDefault(); go(at() + 1); }
			if (e.key === 'ArrowLeft') { e.preventDefault(); go(at() - 1); }
		});
		sync();
	}

	/* ---------- photo lightbox ---------- */
	/* Built here rather than in the template: it needs no markup of its own, and the
	   pictures it shows are the ones the stage has already loaded. Nothing about the
	   gallery above changes, so the page still scrolls and snaps with scripting off —
	   this is an enhancement on top, not a replacement. */
	var zoomStage = $('#carStage');
	if (zoomStage && zoomStage.querySelector('img')) {
		var pics = $$('img', zoomStage);
		var lb = document.createElement('div');
		lb.className = 'lb';
		lb.setAttribute('role', 'dialog');
		lb.setAttribute('aria-modal', 'true');
		lb.setAttribute('aria-label', 'Vehicle photo');
		lb.innerHTML =
			'<img alt="">' +
			'<button type="button" class="lb-btn lb-close" aria-label="Close">' +
			'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>' +
			'<button type="button" class="lb-btn lb-prev" aria-label="Previous photo">' +
			'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5 8 12l7 7"/></svg></button>' +
			'<button type="button" class="lb-btn lb-next" aria-label="Next photo">' +
			'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg></button>' +
			'<div class="lb-count" aria-live="polite"></div>';
		document.body.appendChild(lb);

		var lbImg = lb.querySelector('img');
		var lbClose = lb.querySelector('.lb-close');
		var lbPrev = lb.querySelector('.lb-prev');
		var lbNext = lb.querySelector('.lb-next');
		var lbCount = lb.querySelector('.lb-count');
		var lbAt = 0;

		var lbShow = function (i) {
			lbAt = Math.max(0, Math.min(pics.length - 1, i));
			/* currentSrc is what the browser actually fetched; src is the fallback for a
			   lazy photo further along the strip that has not been reached yet. */
			lbImg.src = pics[lbAt].currentSrc || pics[lbAt].src;
			lbImg.alt = pics[lbAt].alt || '';
			lbPrev.hidden = lbAt <= 0;
			lbNext.hidden = lbAt >= pics.length - 1;
			lbCount.hidden = pics.length < 2;
			lbCount.textContent = (lbAt + 1) + ' / ' + pics.length;
		};
		var shown = function () {
			return zoomStage.clientWidth ? Math.round(zoomStage.scrollLeft / zoomStage.clientWidth) : 0;
		};
		var lbOpen = function () {
			lbShow(shown());
			lb.classList.add('on');
			document.body.classList.add('lb-open');
			lbClose.focus();
		};
		var lbHide = function () {
			lb.classList.remove('on');
			document.body.classList.remove('lb-open');
			/* Leave the strip on the photo the lightbox ended on. Closing onto a different
			   picture than the one on screen a moment ago reads as the page losing its place. */
			if (zoomStage.classList.contains('is-slider')) {
				zoomStage.scrollLeft = lbAt * zoomStage.clientWidth;
			}
			zoomStage.focus({ preventScroll: true });
		};

		/* A tap, not the end of a swipe.
		   ── Why `click` and not `pointerup` ───────────────────────
		   Opening on pointerup looks right and closes itself instantly on a phone:
		   the browser dispatches the synthesised `click` a moment later, at the same
		   coordinates — which by then are over the overlay that was just opened, so
		   the backdrop handler below shuts it again. It reads as nothing happening.
		   `click` is one event with no follow-up, so the race cannot exist.

		   ── Why the guards ─────────────────────────────────────
		   The stage is a horizontal scroll container. `pointercancel` is the browser
		   saying it has taken the gesture for scrolling, which is exactly a swipe;
		   `drift` catches a drag that never got that far. Without them a swipe would
		   put a full-screen overlay in front of somebody who was only browsing. */
		var dx = 0, dy = 0, drift = 0, swiped = false;
		zoomStage.addEventListener('pointerdown', function (e) {
			dx = e.clientX; dy = e.clientY; drift = 0; swiped = false;
		});
		zoomStage.addEventListener('pointermove', function (e) {
			drift = Math.max(drift, Math.abs(e.clientX - dx) + Math.abs(e.clientY - dy));
		});
		zoomStage.addEventListener('pointercancel', function () { swiped = true; });
		zoomStage.addEventListener('click', function () {
			if (swiped || drift > 12) { return; }
			lbOpen();
		});
		zoomStage.classList.add('can-zoom');
		if (!zoomStage.hasAttribute('tabindex')) { zoomStage.setAttribute('tabindex', '0'); }
		zoomStage.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); lbOpen(); }
		});

		lbPrev.addEventListener('click', function () { lbShow(lbAt - 1); });
		lbNext.addEventListener('click', function () { lbShow(lbAt + 1); });
		lbClose.addEventListener('click', lbHide);
		lb.addEventListener('click', function (e) { if (e.target === lb) { lbHide(); } });
		document.addEventListener('keydown', function (e) {
			if (!lb.classList.contains('on')) { return; }
			if (e.key === 'Escape') { lbHide(); }
			if (e.key === 'ArrowRight') { lbShow(lbAt + 1); }
			if (e.key === 'ArrowLeft') { lbShow(lbAt - 1); }
		});
	}
	/* ---------- today's opening hours ---------- */
	var today = new Date().getDay();
	$$('#hoursTable tr').forEach(function (tr) {
		if (parseInt(tr.getAttribute('data-day'), 10) === today) { tr.classList.add('today'); }
	});

	/* ---------- reveal on scroll ---------- */
	var reveals = $$('.rv');
	if ('IntersectionObserver' in window && reveals.length) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('in');
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.12 });
		reveals.forEach(function (el) { io.observe(el); });
	} else {
		reveals.forEach(function (el) { el.classList.add('in'); });
	}
})();
