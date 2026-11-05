# Hatnikotni Chat 0.1.18 — Staging handoff

## Scope
- Open both WhatsApp entry points (floating button and shortcode Contact Us link) in a new tab.
- Add `rel="noopener noreferrer"`.
- No intended change to routing selection, consent, analytics schema, retention, or reporting.

## Navigation behavior
The originating Jelajah Perlis page should remain in its existing tab. The newly opened tab first requests the local WordPress `admin-post.php` click endpoint; that endpoint resolves the configured recipient, records the consent-eligible click event, and redirects the new tab to `wa.me`.

## Staging verification
- [ ] Confirm installed version 0.1.18.
- [ ] Floating WhatsApp button opens new tab.
- [ ] Shortcode Contact Us opens new tab.
- [ ] Original page remains open and unchanged.
- [ ] Consent accepted/rejected paths both permit WhatsApp navigation.
- [ ] Direct routing recipient remains the configured Default.
- [ ] Round Robin test includes the Default when active; record sequential recipients.
- [ ] Analytics history and event attribution behave as expected.

## Checks performed for this handoff
Source change is limited to anchor navigation attributes and version/documentation updates. PHP lint and archive integrity are checked during packaging. Browser-level verification must be performed on staging.

## Release status
Staging candidate only. Not release-approved.
