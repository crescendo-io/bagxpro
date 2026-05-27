(function () {
	'use strict';

	var OPEN_SELECTOR = '.modal-configurator-button, a[href="#modal-configurator"]';
	var root = null;
	var panel = null;
	var closeBtn = null;
	var lastFocus = null;
	var transitionMs = 360;
	var initialized = false;

	function getRoot() {
		if (!root) {
			root = document.getElementById('modal-configurator');
		}
		return root;
	}

	function isOpenTrigger(el) {
		if (!el || el.nodeType !== 1) {
			return false;
		}
		if (el.matches(OPEN_SELECTOR)) {
			return true;
		}
		return !!el.closest(OPEN_SELECTOR);
	}

	function openSidebar() {
		var modal = getRoot();
		if (!modal || modal.classList.contains('is-open')) {
			return;
		}

		lastFocus = document.activeElement;
		modal.removeAttribute('hidden');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('bagxpro-configurator-open');

		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				modal.classList.add('is-open');
			});
		});

		if (closeBtn) {
			window.setTimeout(function () {
				closeBtn.focus();
			}, transitionMs);
		}
	}

	function closeSidebar() {
		var modal = getRoot();
		if (!modal || !modal.classList.contains('is-open')) {
			return;
		}

		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('bagxpro-configurator-open');

		window.setTimeout(function () {
			if (!modal.classList.contains('is-open')) {
				modal.setAttribute('hidden', 'hidden');
			}
			if (lastFocus && typeof lastFocus.focus === 'function') {
				lastFocus.focus();
			}
		}, transitionMs);
	}

	function bindModalEvents() {
		var modal = getRoot();
		if (!modal || initialized) {
			return;
		}

		initialized = true;
		panel = modal.querySelector('.bagxpro-configurator__panel');
		closeBtn = modal.querySelector('.bagxpro-configurator__close');

		var closeTriggers = modal.querySelectorAll('[data-bagxpro-configurator-close]');
		for (var j = 0; j < closeTriggers.length; j++) {
			closeTriggers[j].addEventListener('click', closeSidebar);
		}
	}

	function init() {
		bindModalEvents();

		document.addEventListener('click', function (event) {
			var trigger = event.target;
			if (!isOpenTrigger(trigger)) {
				return;
			}

			var link = trigger.closest ? trigger.closest(OPEN_SELECTOR) : trigger;
			if (!link) {
				return;
			}

			event.preventDefault();
			openSidebar();
		});

		document.addEventListener('keydown', function (event) {
			var modal = getRoot();
			if (!modal) {
				return;
			}

			if (event.key === 'Escape' && modal.classList.contains('is-open')) {
				closeSidebar();
				return;
			}

			if (event.key === 'Tab' && modal.classList.contains('is-open') && panel) {
				var focusable = panel.querySelectorAll(
					'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
				);
				if (!focusable.length) {
					return;
				}
				var first = focusable[0];
				var last = focusable[focusable.length - 1];
				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
