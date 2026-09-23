=== Hatnikotni Chat ===
Requires at least: 6.6
Requires PHP: 8.1
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight WhatsApp contact routing with consent-aware first-party interaction analytics and UTM campaign attribution.

== Description ==

Hatnikotni Chat provides a lightweight WhatsApp contact layer for WordPress sites.

Features include:

* Floating WhatsApp button.
* Shortcode: [hatnikotni_chat]
* Contact management.
* Direct, random and round-robin routing.
* Consent-aware first-party WhatsApp click analytics.
* UTM campaign attribution.
* 180-day analytics event retention.
* WordPress Privacy Policy Guide integration.
* Extension foundation for future integrations.

The plugin does not require an external analytics service.

== Privacy ==

Hatnikotni Chat is designed so visitor analytics is opt-in.

Analytics and campaign attribution remain disabled unless the site provides an explicit visitor consent signal through the hkc_has_analytics_consent filter. The plugin does not provide its own consent banner.

When consent is granted, the plugin may store:

* Selected contact ID.
* WordPress page ID and page type.
* Broad device category: mobile, tablet or desktop.
* utm_source, utm_medium, utm_campaign, utm_term and utm_content.

The plugin does not intentionally store IP addresses, visitor names, phone numbers, email addresses, full user-agent strings, fingerprints, visitor IDs, browsing history or WhatsApp conversation content.

Analytics events are stored in the site's WordPress database and retained for 180 days. The hkc_campaign first-party cookie may retain the latest supported UTM attribution for up to 30 days when consent is available.

When a visitor chooses to contact the site through WhatsApp, the browser is redirected to WhatsApp. WhatsApp's own privacy policy and terms apply to that interaction.

The plugin also provides suggested privacy-policy text through WordPress's Privacy Policy Guide.

== Installation ==

1. Install and activate Hatnikotni Chat.
2. Open Hatnikotni Chat in wp-admin.
3. Add at least one active contact.
4. Configure the default contact, message, button and routing settings.
5. Configure the site's consent mechanism to return true through the hkc_has_analytics_consent filter if visitor analytics is desired.
6. Test the WhatsApp button and routing on staging before production use.

== Frequently Asked Questions ==

= Does analytics run automatically? =

No. Visitor analytics is disabled unless the site provides an explicit consent signal through the hkc_has_analytics_consent filter.

= Does the plugin send analytics to an external service? =

No. Core analytics is stored locally in the site's WordPress database.

= Does a WhatsApp click prove that a message was sent? =

No. The event records that the visitor clicked the WhatsApp action. It does not confirm message delivery or conversation activity.

= Can I use the plugin without analytics? =

Yes. The WhatsApp button, shortcode, contact management and routing work without visitor analytics consent.

= Does the plugin depend on WooCommerce? =

No. WooCommerce is a future optional integration and is not required by the core plugin.

== Screenshots ==

1. Floating WhatsApp button and routing.
2. Contact management.
3. General settings.
4. WhatsApp interaction analytics.

== Changelog ==

= Development =
* Added consent-aware analytics and campaign attribution.
* Added WordPress Privacy Policy Guide integration.
* Added WordPress.org readiness documentation and readme.

== Upgrade Notice ==

= Development =
Development build. Complete staging and release validation is still required before production use.
