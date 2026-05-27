(function () {
	'use strict';

	function parseTarget(el) {
		var raw = el.getAttribute('data-bagxpro-count');
		if (!raw) {
			return NaN;
		}
		return parseInt(raw, 10);
	}

	function formatNumber(n) {
		try {
			return new Intl.NumberFormat('fr-FR').format(n);
		} catch (e) {
			return String(n);
		}
	}

	function easeOutCubic(t) {
		return 1 - Math.pow(1 - t, 3);
	}

	function animateCount(el, target, duration) {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			el.textContent = formatNumber(target);
			return;
		}

		var startTs = null;

		function step(ts) {
			if (!startTs) {
				startTs = ts;
			}
			var progress = Math.min((ts - startTs) / duration, 1);
			var value = Math.round(easeOutCubic(progress) * target);
			el.textContent = formatNumber(value);
			if (progress < 1) {
				window.requestAnimationFrame(step);
			} else {
				el.textContent = formatNumber(target);
			}
		}

		window.requestAnimationFrame(step);
	}

	function init() {
		var section = document.querySelector('.section-cards[data-bagxpro-stats]');
		if (!section) {
			return;
		}

		var cells = section.querySelectorAll('.card-number[data-bagxpro-count]');
		if (!cells.length) {
			return;
		}

		var played = false;

		function play() {
			if (played) {
				return;
			}
			played = true;

			for (var i = 0; i < cells.length; i++) {
				(function (el, index) {
					var target = parseTarget(el);
					if (isNaN(target) || target < 1) {
						return;
					}
					window.setTimeout(function () {
						animateCount(el, target, 2200);
					}, index * 140);
				})(cells[i], i);
			}
		}

		if (!('IntersectionObserver' in window)) {
			play();
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				for (var j = 0; j < entries.length; j++) {
					if (entries[j].isIntersecting) {
						play();
						observer.disconnect();
						break;
					}
				}
			},
			{
				threshold: 0.2,
				rootMargin: '0px 0px -5% 0px',
			}
		);

		observer.observe(section);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
