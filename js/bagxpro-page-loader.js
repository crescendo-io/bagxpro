(function () {
	'use strict';

	var loader = document.getElementById('bagxpro-page-loader');
	var body = document.body;

	function markLoaderDone() {
		if (body) {
			body.classList.add('bagxpro-loader-done');
		}
		document.dispatchEvent(new CustomEvent('bagxpro:loader-done'));
	}

	if (!loader) {
		markLoaderDone();
		return;
	}
	var minDisplayMs = 550;
	var maxDisplayMs = 8000;
	var startedAt = Date.now();
	var finished = false;
	var fadeMs = 450;

	function finish() {
		if (finished) {
			return;
		}
		finished = true;
		loader.classList.add('is-hidden');
		if (body) {
			body.classList.remove('bagxpro-is-loading');
		}
		window.setTimeout(function () {
			if (body) {
				body.classList.add('bagxpro-loader-done');
			}
			document.dispatchEvent(new CustomEvent('bagxpro:loader-done'));
			if (loader.parentNode) {
				loader.parentNode.removeChild(loader);
			}
		}, fadeMs);
	}

	function finishImmediately() {
		if (finished) {
			return;
		}
		finished = true;
		loader.classList.add('is-hidden');
		if (body) {
			body.classList.remove('bagxpro-is-loading');
			body.classList.add('bagxpro-loader-done');
		}
		document.dispatchEvent(new CustomEvent('bagxpro:loader-done'));
		if (loader.parentNode) {
			loader.parentNode.removeChild(loader);
		}
	}

	function finishAfterMinDelay() {
		var elapsed = Date.now() - startedAt;
		var remaining = Math.max(0, minDisplayMs - elapsed);
		window.setTimeout(finish, remaining);
	}

	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		finishImmediately();
		return;
	}

	if (document.readyState === 'complete') {
		finishAfterMinDelay();
	} else {
		window.addEventListener('load', finishAfterMinDelay);
	}

	window.setTimeout(finish, maxDisplayMs);
})();
