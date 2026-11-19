/**
 * Optional greeting card for Hatnikotni Chat (concept C, off by default).
 *
 * Shows the card once per browser session after the configured delay. It
 * never moves focus on its own, sets no cookies, and only uses
 * sessionStorage to remember that the card was already shown.
 */
(function () {
	'use strict';

	var storageKey = 'hatnch_greeting_shown';

	function wasShown() {
		try {
			return '1' === window.sessionStorage.getItem(storageKey);
		} catch (error) {
			return false;
		}
	}

	function markShown() {
		try {
			window.sessionStorage.setItem(storageKey, '1');
		} catch (error) {
			// Storage can be unavailable (privacy mode); the card then shows once per page view.
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		var card = document.getElementById('hatnch-greeting');
		if (!card) {
			return;
		}

		var widget = card.closest('.hatnch-widget');
		var closeButton = card.querySelector('.hatnch-greeting__close');
		var cta = card.querySelector('[data-hatnch-greeting-cta]');
		var mainAction = widget ? widget.querySelector('.hatnch-floating') : null;
		var privacyToggle = widget ? widget.querySelector('.hatnch-privacy-toggle') : null;
		var dismissed = false;
		var delay = parseInt(card.getAttribute('data-hatnch-greeting-delay'), 10);

		if (isNaN(delay) || delay < 0) {
			delay = 5;
		}

		function hide(returnFocus) {
			var hadFocus = card.contains(document.activeElement);
			dismissed = true;
			card.hidden = true;
			if ((returnFocus || hadFocus) && mainAction) {
				mainAction.focus();
			}
		}

		if (closeButton) {
			closeButton.addEventListener('click', function () {
				hide(true);
			});
		}

		if (cta) {
			cta.addEventListener('click', function (event) {
				if ('BUTTON' === cta.tagName && mainAction) {
					// Purpose routing: open the existing purpose chooser instead of navigating.
					// Stop the click reaching the chooser's outside-click handler, which would close it again.
					event.stopPropagation();
					hide(false);
					mainAction.click();
					return;
				}
				hide(false);
			});
		}

		// Any other way of engaging with the widget retires the card.
		if (mainAction) {
			mainAction.addEventListener('click', function () {
				dismissed = true;
				card.hidden = true;
			});
		}
		if (privacyToggle) {
			privacyToggle.addEventListener('click', function () {
				dismissed = true;
				card.hidden = true;
			});
		}

		document.addEventListener('keydown', function (event) {
			if ('Escape' === event.key && !card.hidden) {
				hide(false);
			}
		});

		if (wasShown()) {
			return;
		}

		window.setTimeout(function () {
			if (dismissed) {
				return;
			}
			var privacyPanel = widget ? widget.querySelector('.hatnch-privacy-panel') : null;
			var purposePanel = document.getElementById('hatnch-purpose-panel');
			if ((privacyPanel && !privacyPanel.hidden) || (purposePanel && !purposePanel.hidden)) {
				return;
			}
			card.hidden = false;
			markShown();
		}, delay * 1000);
	});
}());
