(function () {
	'use strict';

	var body = document.body;
	if (!body) {
		return;
	}

	function getNavRoot() {
		return document.querySelector('.menu-navigation');
	}

	function setNavOpen(isOpen) {
		body.classList.toggle('nav-is-visible', isOpen);
		var nav = getNavRoot();
		if (nav) {
			nav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
		}
	}

	function isNavOpen() {
		return body.classList.contains('nav-is-visible');
	}

	function toggleNav() {
		setNavOpen(!isNavOpen());
	}

	function closeNav() {
		setNavOpen(false);
	}

	document.addEventListener(
		'click',
		function (event) {
			var closeBtn = event.target.closest('[data-bagxpro-menu-close]');
			if (closeBtn) {
				event.preventDefault();
				event.stopPropagation();
				closeNav();
				return;
			}

			var trigger = event.target.closest('.button-menu');
			if (trigger) {
				event.preventDefault();
				event.stopPropagation();
				event.stopImmediatePropagation();
				toggleNav();
				return;
			}

			if (isNavOpen()) {
				var insideNav = event.target.closest('.menu-navigation, .button-menu');
				if (!insideNav) {
					closeNav();
				}
			}
		},
		true
	);

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && isNavOpen()) {
			closeNav();
		}

		if ((event.key === 'Enter' || event.key === ' ') && event.target.closest('.button-menu')) {
			event.preventDefault();
			toggleNav();
		}
	});

	if (window.jQuery) {
		window.jQuery(function ($) {
			$('.button-menu').off('click');
		});
	}
})();
