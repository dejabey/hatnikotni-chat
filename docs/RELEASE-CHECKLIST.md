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

## Production
- [ ] Full backup
- [ ] Install stable package
- [ ] Configure production contact
- [ ] Verify frontend and WhatsApp redirect
- [ ] Verify consent/analytics
- [ ] Disable old WP Chat App
- [ ] Monitor after activation
- [ ] Keep rollback package available

## Latest validation
- Run #200 passed.
- Latest validation commit before submission: 91e3ce925854036be3c294590ac3c47bc8b80a4e.
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
