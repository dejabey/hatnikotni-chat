# WordPress.org Preflight — Hatnikotni Chat

## Current state
- Version: 0.1.0
- Branch: release/0.1.0-rc1
- Latest validation before submission: Run #200 — success
- Latest post-submission documentation validation: Run #206 — success
- Release commit: 3120a5e69d102a43713435b3dcf689d66decbfda
- Stable Git tag: 0.1.0
- Contributor: zaryl
- Tested up to: 7.1
- Stable tag: 0.1.0
- WordPress.org submission date: September 26, 2026
- Assigned slug: hatnikotni-chat
- Current review status: Awaiting Review
- Automated Plugin Scanning: Pass

## Completed
- GPLv2 or later
- WordPress-native APIs
- No external analytics service
- Consent-aware analytics
- No third-party runtime JS/CSS dependency
- Privacy Policy Guide integration
- Uninstall cleanup
- Runtime-only package
- Staging validation reported complete by user
- Official WordPress.org Readme Validator run completed; no validation error was shown, only the informational no-donate-link note
- Plugin Check completed; 0 Errors, 39 warnings
- Stable package submitted to WordPress.org

## Remaining
1. Await manual WordPress.org review.
2. If approved, complete directory/SVN release and asset placement steps.

## Post-approval release plan
See docs/RELEASE-CHECKLIST.md for the detailed SVN runbook covering trunk, approved readme/runtime files, screenshot assets, tags/0.1.0, directory verification and recording the final SVN release information.

## Production gate
Production remains untouched. Do not migrate until the stable release is finalized and the user explicitly approves production migration.

## UI
Admin UI is frozen after staging verification. Do not redesign unless a concrete defect is discovered.


## Review Round 1 — 0.1.1

The initial 0.1.0 submission was pended for two concrete technical issues: plugin prefix compliance and an invalid Plugin URI. The correction release changes the internal prefix family to HATNCH_ / hatnch_ / hatnch-, removes the Plugin URI, keeps the slug `hatnikotni-chat`, and must be fully tested before upload.
