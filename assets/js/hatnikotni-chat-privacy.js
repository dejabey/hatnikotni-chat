/**
 * Native consent controls for Hatnikotni Chat.
 *
 * Stores only the visitor's explicit analytics choice. WhatsApp navigation
 * remains independent of the choice.
 */
(function () {
	'use strict';

	var cookieName = 'hatnch_analytics_consent';
	var config = window.hatnchPrivacyConfig || {};
	var maxAge = 60 * 60 * 24 * 180;

	function readConsent() {
		var match = document.cookie.match(/(?:^|;\s*)hatnch_analytics_consent=(yes|no)(?:;|$)/);
		return match ? match[1] : '';
	}

	function writeConsent(value) {
		var cookie = cookieName + '=' + value + '; Max-Age=' + maxAge + '; Path=/; SameSite=Lax';
		if (window.location.protocol === 'https:') {
			cookie += '; Secure';
		}
		document.cookie = cookie;
	}

	function clearCampaignCookie() {
		var path = config.cookiePath || '/';
		var domain = config.cookieDomain || '';
		var cookie = 'hatnch_campaign=; Max-Age=0; Path=' + path + '; SameSite=Lax';
		if (domain) {
			cookie += '; Domain=' + domain;
		}
		if (window.location.protocol === 'https:') {
			cookie += '; Secure';
		}
		document.cookie = cookie;
	}

	function closePanel(panel, toggle) {
		panel.hidden = true;
		toggle.setAttribute('aria-expanded', 'false');
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.hatnch-widget').forEach(function (widget) {
			var toggle = widget.querySelector('.hatnch-privacy-toggle');
			var panel = widget.querySelector('.hatnch-privacy-panel');
			var consent = widget.querySelector('.hatnch-privacy-consent-input');
			var status = widget.querySelector('.hatnch-privacy-status');

			if (!toggle || !panel || !consent) {
				return;
			}

			consent.checked = 'yes' === readConsent();

			toggle.addEventListener('click', function () {
				var willOpen = panel.hidden;
				panel.hidden = !willOpen;
				toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
				if (willOpen) {
					consent.focus();
				}
			});

			consent.addEventListener('change', function () {
				if (consent.checked) {
					writeConsent('yes');
					if (status) {
						status.textContent = config.consentAccepted || 'Analytics accepted.';
					}
					return;
				}

				writeConsent('no');
				clearCampaignCookie();
				if (status) {
					status.textContent = config.consentRejected || 'Analytics rejected. WhatsApp remains available.';
				}
			});

			document.addEventListener('keydown', function (event) {
				if ('Escape' === event.key && !panel.hidden) {
					closePanel(panel, toggle);
					toggle.focus();
				}
			});
		});
	});
}());
