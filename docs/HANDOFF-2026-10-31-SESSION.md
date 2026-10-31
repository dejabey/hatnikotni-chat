# Session Resume — Hatnikotni Chat
Date: 2026-10-31
Status: In progress. Production deployment is not authorized.

## Critical decisions
- Candidate 0.1.24 is not UX-accepted. User installed it and observed no meaningful visual improvement.
- Do not treat a version bump or a passing syntax check as proof of UX success.
- Do not overwrite the default branch or merge the candidate without reconciling source history.
- Avoid storing contact names, phone-like values, or unnecessary account details in this handoff.

## User-supplied candidate series
Versions 0.1.19, 0.1.20, 0.1.21, 0.1.22, 0.1.23 and 0.1.24 were provided and inventoried in the previous session.

Recorded file-diff observations:
- 0.1.19 to 0.1.20: workspace/extensions architecture and routing extensibility.
- 0.1.20 to 0.1.21: navigation, tab persistence, privacy UI refinements.
- 0.1.21 to 0.1.22: navigation/support URL and privacy UX refinements.
- 0.1.22 to 0.1.23: frontend CSS changes; no PHP changes in the inspected comparison.
- 0.1.23 to 0.1.24: Contacts scroll hint removal; Analytics table styling changes; release metadata/docs updates.
- Correction: admin PHP also changed in 0.1.24 to remove the Contacts hint paragraph.
- v0.1.23 and v0.1.24 frontend CSS are identical.
- Contacts retains a minimum table width of 800px. Analytics layout changes need visual overflow/clipping checks.
- These findings are scoped to inspected files; full archive reconciliation is pending.

## GitHub current picture
Repository: dejabey/hatnikotni-chat; default branch: main; repository accessible with push permission at the time of inspection.
Open draft PRs observed:
- PR #1: WordPress.org prefix remediation.
- PR #2: WordPress.org prefix remediation, base main, head wordpress-org-compliance.
- PR #3: Legacy migration hardening, base wordpress-org-compliance, head migration-hardening.
- PR #4: Compact responsive contact form UI, base migration-hardening, head ui-compact-contact-form.
Do not merge or retarget without reviewing dependencies and branch ancestry. PR #3 and #4 are staged as dependent work. Verify all states anew on resumption.

A docs-only branch was created: docs/handoff-2026-10-31-version-audit. Confirm it exists and inspect its HEAD before relying on it. This session's attempt to create a handoff file was blocked by a safety check; do not claim the document is in GitHub.

## Budget alert
User screenshot showed five usage budgets at 100% and “Usage blocked” (AI credit SKUs, Codespaces, Packages, Actions, Git LFS). This does not prove repository reads/writes or the GitHub connection are blocked. User expects reset around month end; exact reset moment/timezone/restoration remain unverified. Test current status; record actual results.

## UX requirements
Preserve routing, consent, persistence, contact CRUD, shortcode and extension behavior.
- Admin General, Contacts and Analytics navigation must remain accessible.
- Contacts needs a real responsive information hierarchy; removing a hint alone is inadequate.
- Analytics needs readable layout without clipped data; consider responsive presentation or a clearly usable scroll region.
- Revisit spacing of the rejected-consent message and mobile compactness.
- Test frontend widget/privacy separately from admin views.
- Verify active installed version, cache state and viewport before diagnosing the reported no-change result.
- Compare before/after screenshots at equivalent viewport dimensions.

## Required sequence
1. Verify repository HEAD, branches, open PRs, workflow/CI status and current integration permissions.
2. Reconcile the full trees and behaviors of supplied candidates with GitHub branch ancestry.
3. Establish actual staging UI baselines and installed build identity.
4. Implement focused root-cause changes.
5. Run syntax, packaging, security, contract/regression and CI checks.
6. Test staging: direct/random/round-robin, inactive contacts, consent grant/reject/withdraw, analytics fail-open, shortcode, attribution, caching, coexistence, retention, accessibility, responsive desktop/mobile.
7. Update changelog, test report, documentation and authoritative current handoff.
8. Prepare a traceable source PR/release candidate. Require user staging acceptance before any production discussion.

## Evidence labels
Candidate inventory and user report of no visible v0.1.24 improvement: CONFIRMED.
File observations: CONFIRMED within inspected comparison scope.
Full source reconciliation, current budget reset, UX fix, staging acceptance and PR/CI readiness: PENDING.
Production deployment: NOT AUTHORIZED.

## Resume instruction
Continue the audit, verify live GitHub state, reconcile candidate histories, then repair and prove responsive Contacts/Analytics and consent UI. Update the canonical handoff and do not deploy to production.
