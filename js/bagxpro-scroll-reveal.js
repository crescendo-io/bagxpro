(function () {
	'use strict';

	var SELECTORS =
		'h1,h2,h3,h4,h5,h6,p,' +
		'li,' +
		'.label,' +
		'.card-label,.card-description,' +
		'.container-image-cards-placeholder,' +
		'.img-card,' +
		'.container-text .button,' +
		'.description-product .button,' +
		'.strate-presentation .container-text ul > li,' +
		'.strate-presentation .col-sm-6 > .img,' +
		'.breadcrumb a,' +
		'.card-intro-product h2,' +
		'.card-intro-product__text';

	var SKIP_ANCESTOR =
		'header, footer, nav, .menu, .strate-hero.home, .bagxpro-no-scroll-reveal, .bagxpro-visually-hidden, [hidden], [aria-hidden="true"]';

	function shouldSkip(el) {
		if (!el || el.nodeType !== 1) {
			return true;
		}
		if (el.closest(SKIP_ANCESTOR)) {
			return true;
		}
		if (el.matches('input, textarea, select, option, button, label, .card-number')) {
			return true;
		}
		if (el.matches('h3, h4, p') && el.closest('.strate-presentation .container-text ul')) {
			return true;
		}
		if (el.matches('p') && el.closest('li') && !el.closest('.description-produit')) {
			return true;
		}
		if (!el.textContent || !String(el.textContent).replace(/\s/g, '').length) {
			return true;
		}
		return false;
	}

	function isInViewport(el) {
		var rect = el.getBoundingClientRect();
		var vh = window.innerHeight || document.documentElement.clientHeight;
		return rect.top < vh * 0.88 && rect.bottom > 0;
	}

	function collectTargets() {
		var nodes = document.body.querySelectorAll(SELECTORS);
		var targets = [];

		for (var i = 0; i < nodes.length; i++) {
			var el = nodes[i];
			if (shouldSkip(el)) {
				continue;
			}
			if (el.classList.contains('bagxpro-scroll-reveal')) {
				targets.push(el);
				continue;
			}
			el.classList.add('bagxpro-scroll-reveal');
			targets.push(el);
		}

		return targets;
	}

	function init() {
		if (!document.body) {
			return;
		}

		var targets = collectTargets();
		if (!targets.length) {
			return;
		}

		function revealAll() {
			for (var j = 0; j < targets.length; j++) {
				targets[j].classList.add('is-visible');
			}
		}

		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			revealAll();
			return;
		}

		var toObserve = [];

		for (var k = 0; k < targets.length; k++) {
			if (isInViewport(targets[k])) {
				targets[k].classList.add('is-visible');
			} else {
				toObserve.push(targets[k]);
			}
		}

		if (!toObserve.length) {
			return;
		}

		if (!('IntersectionObserver' in window)) {
			for (var m = 0; m < toObserve.length; m++) {
				toObserve[m].classList.add('is-visible');
			}
			return;
		}

		var observer = new IntersectionObserver(
			function (entries) {
				for (var n = 0; n < entries.length; n++) {
					if (entries[n].isIntersecting) {
						entries[n].target.classList.add('is-visible');
						observer.unobserve(entries[n].target);
					}
				}
			},
			{
				threshold: 0.12,
				rootMargin: '0px 0px -8% 0px',
			}
		);

		for (var o = 0; o < toObserve.length; o++) {
			observer.observe(toObserve[o]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
