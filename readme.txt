=== Hatnikotni Chat ===
Requires at least: 6.6
Requires PHP: 8.1
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight WhatsApp contact routing with explicit visitor analytics choices and first-party campaign attribution.

== Description ==

Hatnikotni Chat provides a lightweight WhatsApp contact layer for WordPress sites. Its focus is contact routing (direct, random and round-robin), consent-gated first-party click analytics, and UTM campaign attribution without an external analytics service.

Features include:

* Floating WhatsApp button.
* Shortcode: [hatnikotni_chat]
* Contact management.
* Direct, random and round-robin routing.
* Explicit opt-in/opt-out controls for first-party WhatsApp click analytics.
* UTM campaign attribution when analytics consent is granted.
* 180-day analytics event retention.
* WordPress Privacy Policy Guide integration.
* Extension foundation for future integrations.

The plugin does not require an external analytics service. The WhatsApp button works regardless of the visitor's analytics choice.

== Privacy ==

Hatnikotni Chat includes a compact Privacy choices control beside the floating WhatsApp button. Visitors can explicitly allow or reject optional analytics, expand a short explanation of the data recorded, and open the site's WordPress Privacy Policy page when one is configured. The WhatsApp action remains available regardless of the choice.

Analytics and campaign attribution remain disabled when no explicit allow choice exists. The visitor's choice is stored in a first-party cookie named hatnch_analytics_consent for up to 180 days. The site can integrate or override the consent signal through the hatnch_has_analytics_consent filter.

When consent is granted, the plugin may store:

* Selected contact ID.
* WordPress page ID and page type.
* Broad device category: mobile, tablet or desktop.
* utm_source, utm_medium, utm_campaign, utm_term and utm_content.

The plugin does not intentionally store IP addresses, visitor names, phone numbers, email addresses, full user-agent strings, fingerprints, visitor IDs, browsing history or WhatsApp conversation content.

Analytics events are stored in the site's WordPress database and retained for 180 days. The hatnch_campaign first-party cookie may retain the latest supported UTM attribution for up to 30 days when consent is available. Rejecting analytics expires the campaign cookie in the browser and the server also clears it on the next request where possible.

When a visitor chooses to contact the site through WhatsApp, the browser is redirected to WhatsApp. WhatsApp's own privacy policy and terms apply to that interaction.

The plugin also provides suggested privacy-policy text through WordPress's Privacy Policy Guide. English is the source language. Translations use the `hatnikotni-chat` text domain and the site's WordPress locale; WordPress.org language packs are supported. English is the source language. Translations use the `hatnikotni-chat` text domain and the site's WordPress locale; WordPress.org language packs are supported.

== Installation ==

1. Install and activate Hatnikotni Chat.
2. Open Hatnikotni Chat in wp-admin.
3. Add at least one active contact.
4. Configure the default contact, message, button and routing settings.
5. Test the privacy choices, WhatsApp button and routing on staging before production use.

== Frequently Asked Questions ==

= Does analytics run automatically? =

No. Analytics is disabled until the visitor explicitly allows it through the plugin's Privacy choices control, unless the site deliberately integrates a consent signal through the hatnch_has_analytics_consent filter.

= Does rejecting analytics block WhatsApp? =

No. Visitors can still use the WhatsApp button and shortcode. Rejecting analytics only disables optional analytics and campaign attribution.

= Can a visitor change their choice? =

Yes. The Privacy choices control remains available beside the floating button so visitors can change their selection. The latest choice is stored for up to 180 days in that browser.

= Does the plugin send analytics to an external service? =

No. Core analytics is stored locally in the site's WordPress database.

= Does a WhatsApp click prove that a message was sent? =

No. The event records that the visitor clicked the WhatsApp action. It does not confirm message delivery or conversation activity.

= Does the plugin depend on WooCommerce? =

No. WooCommerce is a future optional integration and is not required by the core plugin.

== Screenshots ==

1. Floating WhatsApp button and privacy choices.
2. Contact management.
3. General settings.
4. WhatsApp interaction analytics.

== Changelog ==

= 0.1.8 =
* Fixed production ZIP structure so the archive contains the stable `hatnikotni-chat/` plugin directory.
* Added a packaging contract check to prevent version-specific install folders and accidental duplicate installations.

= 0.1.7 =
* Set English as the source language for plugin interface strings.
* Added Malay translations for the interface and consent status messages.
* Added text-domain loading and compiled translations in the production package.
* Preserved the Contact Us fatal-error fix and compact privacy panel.

= 0.1.6 =
* Fixed the fatal class-name typo in the WhatsApp click handler.
* Reduced the privacy panel to 250px maximum width and tightened typography/padding.
* Made the “Privasi” control quieter and renamed the disclosure to “Butiran”.

= 0.1.5 =
* Localized the consent panel labels and status messages in Malay.
* Shortened the floating privacy control label to “Privasi”.
* Reduced panel width and spacing while preserving readable consent details.

= 0.1.4 =
* Replaced separate analytics action buttons with a compact, accessible Reject/Accept toggle.
* Added a Read more disclosure beside the Analytics heading.
* Preserved explicit consent, saved preference, and WhatsApp availability regardless of analytics choice.

= 0.1.3 =
* Refined the native privacy panel with shorter copy, tighter spacing and responsive sizing.
* Kept explicit Allow analytics and Reject analytics choices and the expandable data-use explanation.
* Kept WhatsApp routing available regardless of analytics choice.

= 0.1.2 =
* Added native visitor analytics privacy choices with explicit allow/reject actions.
* Kept WhatsApp routing available regardless of analytics choice.
* Added a collapsible data-use explanation and link to the site's Privacy Policy page.
* Added a 180-day first-party consent preference cookie and updated privacy documentation.

= 0.1.1 =
* Migrated plugin declarations and stored data to the unique hatnch namespace for WordPress.org compatibility.
* Added migration handling for existing settings, routing state and custom tables.
* Added explicit WordPress.org prefix compliance documentation.
* Added consent-aware analytics and campaign attribution.
* Added WordPress Privacy Policy Guide integration.
* Added WordPress.org readiness documentation and readme.

== Upgrade Notice ==

= 0.1.8 =
Fixes the ZIP directory structure so WordPress can identify the package as an update to the existing Hatnikotni Chat plugin. Back up first and test on staging.

= 0.1.6 =
Fixes the Contact Us critical error and further compacts the privacy controls. Validate the WhatsApp redirect and both consent states on staging before production use.
