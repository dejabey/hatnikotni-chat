=== Hatnikotni Chat ===
Requires at least: 6.6
Requires PHP: 8.1
Tested up to: 7.1
Stable tag: 0.1.26
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight WhatsApp contact routing with explicit visitor analytics choices and first-party campaign attribution.

== Changelog ==

= 0.1.26 =
* Fix status-update handling when a contact record no longer exists.
* Persist and verify Round Robin cursors atomically in the database while holding the routing advisory lock, avoiding stale option-cache reads.
* Add a concurrent WordPress/MySQL regression test for twelve simultaneous Round Robin requests.
* Sanitize purpose-role input and harden analytics action verification and admin HTTP error responses.
* Remove temporary Round Robin QA diagnostics.

== Description ==

Hatnikotni Chat is a focused WhatsApp enquiry-routing tool for WordPress sites. Rather than serving as a general social-sharing button, it combines configurable contact routing (direct, random and round-robin) with optional first-party click analytics and UTM campaign attribution. Analytics remains disabled until the visitor explicitly opts in, and WhatsApp stays usable when analytics is rejected. Core analytics is stored locally; no external analytics service is required.

Features include:

* Floating WhatsApp button.
* Shortcode: [hatnikotni_chat]
* Contact management with edit, activation controls and permanent deletion.
* Direct, random and round-robin routing.
* Explicit opt-in/opt-out controls for first-party WhatsApp click analytics.
* UTM campaign attribution when analytics consent is granted.
* 180-day analytics event retention.
* WordPress Privacy Policy Guide integration.
* Extension foundation for future integrations.

The plugin does not require an external analytics service. The WhatsApp button works regardless of the visitor's analytics choice.

== Privacy ==

Hatnikotni Chat includes a small shield icon immediately to the left of the floating WhatsApp button. Selecting it opens a compact consent card where visitors can explicitly allow or reject optional analytics, read a short explanation, and open the site's WordPress Privacy Policy page when one is configured. The WhatsApp action remains available regardless of the choice.

Analytics and campaign attribution remain disabled when no explicit allow choice exists. The visitor's choice is stored in a first-party cookie named hatnch_analytics_consent for up to 180 days. The site can integrate or override the consent signal through the hatnch_has_analytics_consent filter.

When consent is granted, the plugin may store:

* Selected contact ID.
* WordPress page ID and page type.
* Broad device category: mobile, tablet or desktop.
* utm_source, utm_medium, utm_campaign, utm_term and utm_content.

The plugin does not intentionally store IP addresses, visitor names, phone numbers, email addresses, full user-agent strings, fingerprints, visitor IDs, browsing history or WhatsApp conversation content.

Analytics events are stored in the site's WordPress database and retained for 180 days. The hatnch_campaign first-party cookie may retain the latest supported UTM attribution for up to 30 days when consent is available. Rejecting analytics asks the server to expire this HttpOnly cookie immediately. If that request is blocked, the server clears it on the next request while consent remains rejected.

When a visitor chooses to contact the site through WhatsApp, the browser is redirected to WhatsApp. WhatsApp's own privacy policy and terms apply to that interaction.

The plugin also provides suggested privacy-policy text through WordPress's Privacy Policy Guide. English is the source language. Translations use the `hatnikotni-chat` text domain and the site's WordPress locale; WordPress.org language packs are supported.

== Installation ==

1. Install and activate Hatnikotni Chat.
2. Open Hatnikotni Chat in wp-admin.
3. Add at least one active contact.
4. Configure the default contact, message, button and routing settings.
5. Test the privacy choices, WhatsApp button and routing on staging before production use.

== Contact management ==

Contact changes are restricted to administrators with the required capability. Contact creation, editing, activation/deactivation and deletion use nonce-protected administrative requests.

Permanent deletion cannot be undone. Historical analytics events are retained, but their association with the deleted contact is removed. If the deleted contact is configured as Default Contact, that setting is cleared; the plugin does not silently choose another recipient. Direct routing then remains without a recipient until an administrator selects another active contact.

The current Default Contact cannot be deactivated. Select another active Default Contact before deactivating it. The delete confirmation calls out the routing consequence when deleting the current Default Contact.

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

= 0.1.24 =
* Removed redundant table-scroll guidance beneath the Contacts table while preserving its real horizontal scrolling behavior.
* Tightened Analytics table layout: two-column reports wrap long values within their cards and no longer expose unnecessary horizontal scrolling.
* Retained the existing admin workspace navigation and all routing, consent, and analytics behavior.


= 0.1.23 =
* Refined mobile purpose chooser sizing, safe-area spacing, scroll behavior, and option touch targets.
* Improved privacy shield visibility and compacted privacy links.
* Removed theme-derived border styling from the primary WhatsApp action while preserving visible keyboard focus.


= 0.1.19 =
* Added optional purpose-based routing for the floating WhatsApp button.
* Added standardized contact roles, purpose-to-role mapping, and optional support email.
* Preserved legacy shortcode and global routing behavior when the feature is disabled.



= 0.1.8 =
* Fixed production ZIP structure so the archive contains the stable `hatnikotni-chat/` plugin directory.
* Replaced the visible Privacy text control with a compact shield icon beside the WhatsApp button.
* Reduced privacy card horizontal padding and justified the expanded explanation.
* Replaced the admin phone example with generic number 601234567890 without changing saved contacts.
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


== Analytics management ==

Administrators can export aggregate CSV reports for 7, 30, 90, or 180 days. Exports omit contact phone numbers, raw event IDs, and visitor identifiers.

Analytics events can be deleted by inclusive date range (WordPress site timezone) or cleared entirely after typing DELETE. These operations do not remove contacts or routing settings. Analytics retention remains 180 days.

When a contact is permanently deleted, future event attribution retains a numeric, non-identifying contact reference and displays Removed contact #ID. Historical events that were already anonymized without a reference remain Unknown / removed.


## 0.1.18 — Open WhatsApp destination in a new tab (staging candidate)
- Set the inline shortcode Contact Us link and floating WhatsApp button to open the click-tracking endpoint in a new tab.
- Added `rel="noopener noreferrer"` to outbound-opening anchors.
- Kept the existing server-side routing, consent-aware analytics recording, and destination selection unchanged.
- The click opens the local WordPress tracking endpoint in a new tab, which then redirects that tab to WhatsApp.
- Round Robin functional verification remains pending.


== Extension points ==

Hatnikotni Chat is a reusable contact-routing core. Optional companion plugins can extend supported behavior without editing core files.

- `hatnch_purpose_definitions`: Filter visitor-facing purpose definitions. Return an associative array keyed by sanitized purpose ID, each containing a non-empty `label`.
- `hatnch_purpose_role_routes`: Filter purpose ID to contact-role routing mappings. Keys must correspond to registered purpose IDs; values are role IDs.
- `hatnch_allowed_contact_roles`: Filter permitted contact role IDs for purpose routing.
- `hatnch_admin_extension_panels`: Filter extension settings panel descriptors. Each descriptor may contain `title` and a trusted callable `callback` that renders escaped settings markup.
- `hatnch_routing_lock_release_failed`: Action fired if the Round Robin MySQL advisory lock cannot be confirmed as released. Receives the routing state key and the raw `RELEASE_LOCK()` result. Use it for operational monitoring; do not perform routing or database mutations from this callback.

Extension modules must perform their own capability checks, nonce validation, input validation, output escaping, uninstall/data-retention policy, and compatibility checks. Do not trust purpose, role, phone, or contact destination values supplied in public request parameters.

Purpose routing currently provides a configurable route map for the built-in purpose IDs. Registering a custom visitor purpose alone does not add custom admin mapping controls or a form workflow. Extensions supplying new purposes must also provide a valid role route and any required UI/configuration independently.

== Changelog ==

= 0.1.20 =
* Generalized purpose chooser language for broader plugin reuse.
* Added compact keyboard-operable settings tabs and an Extensions settings surface.
* Added filters for purpose definitions, purpose-to-role mappings, permitted roles, and extension admin panels.
* No database schema changes.


= 0.1.21 =

* Added consistent workspace navigation between General, Contacts and Analytics screens.
* Preserved the active Setup, Routing or Extensions tab after settings are saved.
* Refined the privacy notice spacing and shortened its explanatory copy.
* Adjusted the floating privacy control to sit partially behind the WhatsApp action.
* Kept existing consent choice behavior and global routing implementation.
* Added keyboard-operable workspace links and retained existing admin page slugs.
