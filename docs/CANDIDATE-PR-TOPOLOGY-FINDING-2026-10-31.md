# Candidate vs PR Head — Source Topology Finding
Date: 2026-10-31
Scope: Compare v0.1.24 archive file inventory with the current `ui-compact-contact-form` Git tree and inspect candidate routing/UI JavaScript.

## Confirmed divergence
The candidate archive has a different file topology from the PR head. Candidate v0.1.24 contains 40 plugin-relative files. The inspected PR head contains 35 tracked blobs and does not include:
- `assets/js/hatnikotni-chat-admin-tabs.js`
- `assets/js/hatnikotni-chat-purpose.js`
- Several candidate-specific architecture/design/handoff/test documents.

The PR head has its own additional CI/workflow, composer, PHPCS, and migration-integration files. Therefore this is not a simple file-for-file replacement or linear continuation.

## Feature-bearing candidate files
- The candidate `hatnikotni-chat-admin-tabs.js` handles tab activation, ARIA state, keyboard arrow/home/end navigation, and URL tab selection.
- The candidate `hatnikotni-chat-purpose.js` supports a purpose-selection panel.
- The candidate routing class implements purpose resolution with allow-listed routes, configurable role mapping, active-contact filtering, and per-role round-robin state, falling back through configured default behavior.

These capabilities need a direct behavior-level comparison with the current PR lineage. Their omission from the PR tree is a material source-reconciliation warning; file absence alone does not prove every equivalent behavior is absent elsewhere, but no equivalent was established in this review.

## Decision
Do not merge PR #2/#3/#4 as a complete replacement for the v0.1.24 candidate and do not overwrite candidate source into the PR chain blindly. First establish the desired feature contract and compare routing/settings/admin rendering, including purpose mapping, allowed roles, fallback, round-robin isolation, tab keyboard/accessibility, and direct URL behavior.

## Verification state
- Candidate vs PR file topology difference: **CONFIRMED**
- Candidate includes admin tab keyboard behavior: **CONFIRMED by source inspection**
- Candidate includes purpose selection UI and purpose route resolver: **CONFIRMED by source inspection**
- Equivalent complete behavior on PR head: **PENDING**
- Safe consolidated implementation: **PENDING**
- No PR changed or merged in this review.
- Production: **NOT AUTHORIZED**
