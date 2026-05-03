(function () {
	'use strict';

	function initStraps(root) {
		var group = root.querySelector('[data-bagxpro-straps]');
		if (!group) {
			return;
		}

		var swatches = group.querySelectorAll('.bagxpro-swatch');
		for (var i = 0; i < swatches.length; i++) {
			swatches[i].addEventListener('click', function () {
				var btn = this;
				for (var j = 0; j < swatches.length; j++) {
					swatches[j].classList.remove('is-selected');
					swatches[j].setAttribute('aria-pressed', 'false');
				}
				btn.classList.add('is-selected');
				btn.setAttribute('aria-pressed', 'true');
			});
		}
	}

	function initLogo(root) {
		var input = root.querySelector('[data-bagxpro-logo-input]');
		var zone = root.querySelector('[data-bagxpro-logo-zone]');
		var logoSlot = root.querySelector('#bagxpro-bag-logo');
		var clearBtn = root.querySelector('[data-bagxpro-logo-clear]');

		if (!input || !logoSlot) {
			return;
		}

		var logoImg = null;

		function showClear(show) {
			if (clearBtn) {
				clearBtn.hidden = !show;
			}
		}

		function clearLogo() {
			if (logoImg && logoImg.parentNode) {
				logoImg.parentNode.removeChild(logoImg);
				logoImg = null;
			}
			logoSlot.innerHTML = '';
			logoSlot.hidden = true;
			logoSlot.setAttribute('hidden', 'hidden');
			input.value = '';
			showClear(false);
			if (zone) {
				zone.classList.remove('has-logo');
			}
		}

		function applyFile(file) {
			if (!file || !file.type || file.type.indexOf('image/') !== 0) {
				clearLogo();
				return;
			}

			var reader = new FileReader();
			reader.onload = function (e) {
				var url = e.target && e.target.result;
				if (!url) {
					return;
				}
				logoSlot.innerHTML = '';
				logoImg = document.createElement('img');
				logoImg.src = url;
				logoImg.alt = '';
				logoSlot.appendChild(logoImg);
				logoSlot.hidden = false;
				logoSlot.removeAttribute('hidden');
				showClear(true);
				if (zone) {
					zone.classList.add('has-logo');
				}
			};
			reader.readAsDataURL(file);
		}

		input.addEventListener('change', function () {
			var file = input.files && input.files[0];
			applyFile(file);
		});

		if (clearBtn) {
			clearBtn.addEventListener('click', clearLogo);
		}

		if (zone) {
			zone.addEventListener('dragover', function (ev) {
				ev.preventDefault();
				ev.stopPropagation();
				zone.classList.add('is-dragover');
			});

			zone.addEventListener('dragleave', function (ev) {
				ev.preventDefault();
				if (!zone.contains(ev.relatedTarget)) {
					zone.classList.remove('is-dragover');
				}
			});

			zone.addEventListener('drop', function (ev) {
				ev.preventDefault();
				ev.stopPropagation();
				zone.classList.remove('is-dragover');
				var dt = ev.dataTransfer;
				if (!dt || !dt.files || !dt.files[0]) {
					return;
				}
				applyFile(dt.files[0]);
				try {
					input.files = dt.files;
				} catch (err) {
					/* assignation files impossible sur certains navigateurs */
				}
			});
		}
	}

	function init(root) {
		if (!root) {
			return;
		}
		initLogo(root);
		initStraps(root);
	}

	document.addEventListener('DOMContentLoaded', function () {
		var roots = document.querySelectorAll('[data-bagxpro-product-page]');
		for (var i = 0; i < roots.length; i++) {
			init(roots[i]);
		}
	});
})();
