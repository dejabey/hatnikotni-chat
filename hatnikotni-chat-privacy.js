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

	function requestCampaignCookieClear() {
		if (!config.revokeCampaignUrl || typeof window.fetch !== 'function') {
			return;
		}

		window.fetch(config.revokeCampaignUrl, {
			method: 'POST',
			credentials: 'same-origin',
			cache: 'no-store',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: 'action=hatnch_revoke_campaign'
		}).catch(function () {
			// The next request also clears campaign attribution while consent is rejected.
		});
	}

	function requestCampaignCapture() {
		if (!config.captureCampaignUrl || typeof window.fetch !== 'function' || typeof window.URLSearchParams !== 'function') {
			return;
		}

		var currentUrl;
		var endpoint;
		try {
			currentUrl = new URL(window.location.href);
			endpoint = new URL(config.captureCampaignUrl, window.location.href);
		} catch (error) {
			return;
		}

		var fields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
		var hasAttribution = false;
		fields.forEach(function (field) {
			var value = currentUrl.searchParams.get(field);
			if (value) {
				endpoint.searchParams.set(field, value);
				hasAttribution = true;
			}
		});

		if (!hasAttribution) {
			return;
		}

		endpoint.searchParams.set('action', 'hatnch_capture_campaign');
		window.fetch(endpoint.toString(), {
			method: 'GET',
			credentials: 'same-origin',
			cache: 'no-store'
		}).catch(function () {
			// A later page request can capture attribution while consent remains granted.
		});
	}

	function closePanel(panel, toggle) {
		panel.hidden = true;
		toggle.setAttribute('aria-expanded', 'false');

		var readMore = panel.querySelector('.hatnch-privacy-read-more');
		var explanation = panel.querySelector('.hatnch-privacy-explanation');
		if (readMore) {
			readMore.setAttribute('aria-expanded', 'false');
		}
		if (explanation) {
			explanation.hidden = true;
		}
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
				if (willOpen) {
					panel.hidden = false;
					toggle.setAttribute('aria-expanded', 'true');
					consent.focus();
				} else {
					closePanel(panel, toggle);
				}
			});

			var readMore = widget.querySelector('.hatnch-privacy-read-more');
			var explanation = widget.querySelector('.hatnch-privacy-explanation');
			if (readMore && explanation) {
				readMore.addEventListener('click', function () {
					var willExpand = explanation.hidden;
					explanation.hidden = !willExpand;
					readMore.setAttribute('aria-expanded', willExpand ? 'true' : 'false');
				});
			}

			consent.addEventListener('change', function () {
				if (consent.checked) {
					writeConsent('yes');
					requestCampaignCapture();
					if (status) {
						status.textContent = config.consentAccepted || 'Analytics accepted.';
					}
					return;
				}

				writeConsent('no');
				clearCampaignCookie();
				requestCampaignCookieClear();
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
