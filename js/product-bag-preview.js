(function () {
	'use strict';

	function init(root) {
		if (!root) {
			return;
		}

		var input = root.querySelector('[data-bagxpro-logo-input]');
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
		}

		input.addEventListener('change', function () {
			var file = input.files && input.files[0];
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
			};
			reader.readAsDataURL(file);
		});

		if (clearBtn) {
			clearBtn.addEventListener('click', clearLogo);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		var roots = document.querySelectorAll('[data-bagxpro-product-page]');
		for (var i = 0; i < roots.length; i++) {
			init(roots[i]);
		}
	});
})();
