# Hatnikotni Chat — Handoff

## Current release state
- Version: 0.1.0
- Branch: release/0.1.0-rc1
- Release commit: 3120a5e69d102a43713435b3dcf689d66decbfda
- Latest CI before submission: Run #200 — success.
- Latest post-submission documentation CI: Run #206 — success.
- Stable Git tag: 0.1.0 → 3120a5e69d102a43713435b3dcf689d66decbfda
- WordPress.org contributor: zaryl
- Tested up to: 7.1
- Stable tag in readme: 0.1.0
- Official WordPress.org Readme Validator run completed; no validation error was shown, only the informational no-donate-link note.
- Plugin Check completed with 0 Errors and 39 warnings.
- All previously open staging gates are reported passed by the user.
- WordPress.org submission completed on September 26, 2026.
- Assigned WordPress.org slug: hatnikotni-chat.
- Automated Plugin Scanning: Pass.
- Current WordPress.org review status: Awaiting Review.
- Production remains untouched.

## Remaining release work
1. Await manual WordPress.org review/approval.
2. After approval, complete the WordPress.org directory/SVN release steps.
3. Production migration only after explicit approval.

## Project constraints
- Standalone plugin; no hard dependency on Ninja GDPR, WooCommerce, UrbanGo, WPVibe or AI plugins.
- Keep runtime lightweight.
- No external analytics service.
- Analytics is consent-aware and first-party.
- Do not store IP or visitor identity data.
- Preserve capability checks, nonces, sanitization, escaping and safe redirects.
- Do not silently normalize invalid phone formats.
- Do not treat a WhatsApp click as proof a message was sent.
- Keep documentation updated.
- UI is frozen unless a concrete defect appears.

## Production migration
Do not change production yet. The detailed production migration runbook is in docs/RELEASE-CHECKLIST.md. It covers backup, stable package installation, production contact/settings, frontend and WhatsApp verification, consent/analytics verification, disabling WP Chat App only after successful checks, post-activation monitoring and rollback readiness.

## WordPress.org post-approval
The detailed WordPress.org post-approval runbook is in docs/RELEASE-CHECKLIST.md. It covers SVN availability, trunk content, readme/runtime files, screenshot assets, tags/0.1.0, directory verification and documentation of the final SVN release.

## Continuation
Read this file, docs/RELEASE-CHECKLIST.md, docs/WORDPRESS-ORG-PREFLIGHT.md and docs/staging-test-plan.md before continuing. Verify current branch, tag and CI state first.


## WordPress.org Review — 0.1.1

The 0.1.0 submission was pended for prefix compliance and an invalid/private Plugin URI. Version 0.1.1 addresses the reported technical issues by using the HATNCH_ / hatnch_ / hatnch- prefix family and removing the Plugin URI. The plugin slug remains hatnikotni-chat. Full regression testing, Plugin Check, and release verification are required before resubmission.
