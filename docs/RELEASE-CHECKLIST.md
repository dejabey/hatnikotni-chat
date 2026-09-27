# Hatnikotni Chat Release Checklist

## Release candidate / stable preparation
- [x] Runtime-only package structure
- [x] GPLv2-or-later declaration
- [x] Final readme metadata
- [x] uninstall.php
- [x] No development tests/vendor files in production ZIP
- [x] GitHub Actions validation
- [x] Final WordPress.org readme metadata
- [x] Final WordPress.org screenshot set prepared
- [x] Versioned stable Git tag 0.1.0

## Staging
- [x] All staging validation gates reported passed by user

## WordPress.org
- [x] Official Readme Validator completed; no validation error shown
- [x] Plugin Check completed; 0 Errors, 39 warnings
- [x] Stable package submitted to WordPress.org
- [x] Automated Plugin Scanning: Pass
- [ ] Manual WordPress.org review
- [ ] Approval and directory publication
- [ ] After approval: complete WordPress.org SVN directory release and asset placement

## Production
- [ ] Full backup
- [ ] Install stable package
- [ ] Configure production contact
- [ ] Verify frontend and WhatsApp redirect
- [ ] Verify consent/analytics
- [ ] Disable old WP Chat App
- [ ] Monitor after activation
- [ ] Keep rollback package available

## Production migration runbook
1. Confirm WordPress.org review has approved the release, unless an explicit decision is made to deploy the already-tested package manually before approval.
2. Confirm the exact stable package/version to deploy is 0.1.0 and retain the release ZIP as rollback material.
3. Take a full production backup before changing plugins.
4. Install the stable Hatnikotni Chat package without changing unrelated plugins or theme configuration.
5. Activate Hatnikotni Chat and open its admin settings.
6. Add/verify the production Contact and international WhatsApp number.
7. Configure the default Contact, default message, button label, position, visibility and routing method.
8. Verify the floating WhatsApp button on desktop and mobile.
9. Verify the shortcode if used on any production page.
10. Click the WhatsApp action and confirm the browser reaches WhatsApp with the correct contact number/message.
11. Verify analytics/consent behavior according to the production consent mechanism; do not enable analytics merely to bypass consent.
12. Confirm no unexpected PHP/WP errors and no frontend layout regression.
13. Only after Hatnikotni Chat passes the production checks, deactivate the old WP Chat App.
14. Recheck the frontend after deactivation.
15. Monitor the site after activation.
16. Keep the previous plugin package and backup available for rollback.
17. Roll back by restoring the backup/reverting the plugin state if a material production defect appears.

## WordPress.org post-approval runbook
1. Confirm approval email/status and the assigned hatnikotni-chat directory slug.
2. Confirm the WordPress.org SVN repository is available.
3. Prepare the directory trunk content from the approved runtime package/source as required by WordPress.org.
4. Add the approved readme.txt and plugin runtime files.
5. Place the prepared plugin screenshots in the WordPress.org SVN top-level assets directory using the required filenames.
6. Create the tags/0.1.0 release from the approved trunk content.
7. Verify the directory page, readme metadata, screenshots and installation/update information.
8. Do not change the Git stable tag 0.1.0 to a different runtime state.
9. Record the SVN release revision/URL and final directory status in the handoff documentation.

## Latest validation
- Run #206 passed after the post-submission documentation updates.
- Release commit: 3120a5e69d102a43713435b3dcf689d66decbfda.
- Branch: release/0.1.0-rc1.
- Plugin version: 0.1.0.
- WordPress.org contributor: zaryl.
- Tested up to: 7.1.
- Stable tag: 0.1.0.
- Official WordPress.org submission completed on September 26, 2026.
- WordPress.org assigned slug: hatnikotni-chat.
- Submission status: Awaiting Review.
- Automated Plugin Scanning: Pass.
- Production remains untouched.

## Next
1. Await manual WordPress.org review/approval.
2. After approval, complete the WordPress.org directory/SVN release steps.
3. Only after explicit production approval, perform production migration.


## 0.1.1 Review Fixes

- [ ] Audit all declarations, globals, stored data, WordPress hooks/actions/filters and CSS identifiers for prefix compliance.
- [ ] Use HATC_ / hatc_ / hatc- as the plugin prefix family.
- [ ] Remove the invalid/private Plugin URI.
- [ ] Keep the approved-requested slug `hatnikotni-chat` unchanged.
- [ ] Update release metadata to 0.1.1.
- [ ] Run syntax, contract tests, WPCS and Plugin Check.
- [ ] Run full staging regression tests.
- [ ] Double/triple check release ZIP contents.
- [ ] Upload corrected 0.1.1 package and reply to the existing WordPress.org review thread.
