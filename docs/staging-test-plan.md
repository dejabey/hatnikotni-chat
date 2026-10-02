# Staging Test Plan

Run all tests on staging, not production. Record the plugin version, browser, device, result and any relevant console/PHP errors.

## Core functional tests

- [ ] Fresh installation and activation.
- [ ] Upgrade migration from existing data; verify contacts and routing state survive.
- [ ] Direct routing.
- [ ] Random routing.
- [ ] Round-robin order across active contacts.
- [ ] WhatsApp redirect and optional pre-filled message; confirm no critical error and the browser reaches wa.me.
- [ ] Shortcode output and routing.
- [ ] Desktop/mobile visibility and left/right positioning.
- [ ] Disabled state hides the floating widget.

## Native privacy choices

- [ ] With no consent cookie, click analytics does not record an event.
- [ ] Privacy choices control expands/collapses and has correct aria-expanded state.
- [ ] Analytics consent switch starts off when there is no saved consent and reflects a previously saved allow choice.
- [ ] Switch is keyboard accessible and its accessible label/checked state are correct.
- [ ] Read More (Baca Lagi in Malay) expands/collapses the data-use explanation with keyboard and pointer.
- [ ] Privacy icon is immediately left of the WhatsApp button; card uses compact horizontal padding and justified explanation text.
- [ ] Privacy Policy link points to the configured WordPress Privacy Policy page.
- [ ] Switching to Accept writes hatnch_analytics_consent=yes for 180 days.
- [ ] After accepting, a WhatsApp click records one event on the next request.
- [ ] Switching to Reject writes hatnch_analytics_consent=no.
- [ ] After rejecting, WhatsApp still redirects and no new analytics event is recorded.
- [ ] Rejecting expires the campaign cookie and the next request also clears it server-side where possible.
- [ ] Visitor can change from reject to allow and from allow to reject.
- [ ] Missing choice and rejected choice both default to analytics disabled.
- [ ] Shortcode works when consent is absent or rejected.
- [ ] Browser with JavaScript disabled can still use WhatsApp; analytics remains disabled unless an explicit site integration supplies consent.
- [ ] Check cookie path/domain behavior when WordPress is installed in a subdirectory or uses a custom COOKIEPATH/COOKIE_DOMAIN.
- [ ] No console errors, PHP notices or layout overlap on desktop/mobile.
- [ ] Compact privacy panel remains readable on narrow mobile screens and does not cover the WhatsApp action.
- [ ] Keyboard focus is visible; Escape closes the panel and returns focus to its toggle.

## WordPress Plugin Check / package verification

- [x] Production package build #561 succeeded: https://github.com/dejabey/hatnikotni-chat/actions/runs/37030788380. Artifact ID: `11236849218`. Plugin source is unchanged from build #549; builds #550–#561 changed documentation only.
- [x] User ran Tools → Plugin Check on staging and supplied a screenshot reading “Checks complete. No errors found.” with Error and Warning types selected and AI Analysis unchecked (2026-10-02).
- [ ] Record the exact installed version/build used for this screenshot; the screenshot does not display that information. Export the complete report if possible.
- [x] The earlier six warnings were addressed in source: the public UTM query nonce recommendation has a documented PHPCS exception, and uninstall variables use the plugin prefix. The latest screenshot is consistent with these findings being cleared, but exact build correlation remains to be recorded.
- [ ] If any warning reappears in a full report, inspect the exact source line. Do not add a nonce to external UTM campaign URLs because that would break ordinary campaign links. Never execute uninstall as a test on live staging because it deletes plugin data.

## Cache/CDN

- [ ] Page cache enabled.
- [ ] CDN enabled if production uses one.
- [ ] Routing remains dynamic.
- [ ] Analytics action is not cached.
- [ ] Consent choice takes effect on the next request even with cached frontend pages.
- [ ] Settings changes reflect after cache purge.

## WooCommerce

- [ ] Shop/product pages.
- [ ] Cart/checkout.
- [ ] Button does not interfere with WooCommerce UI.
- [ ] Logged-in/logged-out behavior.

## Performance and security

- [ ] No PHP notices/warnings/fatals.
- [ ] No unnecessary external requests.
- [ ] Inputs sanitized and outputs escaped.
- [ ] External redirect constrained to intended wa.me destination.
- [ ] No secrets/API keys.

## Release gate

Do not release until the current branch passes CI, the clean ZIP is inspected, consent allow/reject/withdrawal behavior is confirmed on staging, and critical routing/privacy tests pass. Production deployment requires separate approval.
