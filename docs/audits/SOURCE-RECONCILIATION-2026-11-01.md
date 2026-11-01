# Hatnikotni Chat — Source Reconciliation Checkpoint
**Date:** 2026-11-01 (Asia/Kuala_Lumpur)
**Scope:** Installed candidate v0.1.24 vs GitHub main and PR #4 source.

## Confirmed repository state
- Repository: `dejabey/hatnikotni-chat`; default branch: `main`.
- `main` tree SHA: `06c443a3ddf1e1d6f64c0f16f4ede18d97d60fcd`.
- `main` plugin header declares **0.1.0**, legacy prefix `HKC_*`, source classes `class-hkc-*.php`.
- PR #4: https://github.com/dejabey/hatnikotni-chat/pull/4
- PR head branch: `ui-compact-contact-form`; head `fbc850949892b849bdb8166ec411f85a4281dea7`.
- PR base: `migration-hardening`; base SHA `43e805e6e9e396b0e17223f8e3be00419be1879d`.
- PR remains **OPEN / DRAFT / UNMERGED**.
- PR branch tree uses `HATNCH_*` / `class-hatnch-*.php`, plugin header **0.1.8**, and contains migration integration/contract tests.
- CI Build #737, run `41281158294`, passed for PR head `fbc850949892b849bdb8166ec411f85a4281dea7`: https://github.com/dejabey/hatnikotni-chat/actions/runs/41281158294.

## Candidate archive audit
- Installed candidate reported by user: **v0.1.24**.
- The six supplied candidate archives v0.1.19–v0.1.24 were inspected in the current runtime. Sequential file-level comparison:
  - 0.1.19→0.1.20: 11 changed files, 4 additions; workspace/extensions and routing-related code changes.
  - 0.1.20→0.1.21: 10 changed files, 2 additions; admin navigation/tab persistence/privacy presentation.
  - 0.1.21→0.1.22: 9 changed files, 2 additions; support URL/privacy UX refinements.
  - 0.1.22→0.1.23: 4 changed files; CSS/frontend refinement and release docs/metadata, no PHP file change in observed diff.
  - 0.1.23→0.1.24: 7 changed files, 2 additions; Contacts hint removal, Analytics table CSS, docs/changelog/release metadata.
- All six archives passed ZIP integrity, PHP syntax (13 PHP files each), and JavaScript syntax (2 JS files in 0.1.19; 3 in later versions).
- Candidate 0.1.24 `readme.txt` Stable tag remains `0.1.22`, while plugin header/HATNCH version is 0.1.24. Release metadata requires correction.

## Source lineage conclusion
- GitHub `main` is **not** the source baseline for installed candidate 0.1.24; it is the earlier HKC 0.1.0 implementation.
- PR #4 has the modern HATNCH implementation family and is closer architecturally, but its header is 0.1.8 and its base is migration-hardening. It is **not proven byte-for-byte or commit-lineage equivalent** to the installed 0.1.24 candidate.
- Therefore PR #4 must not be merged or transplanted wholesale into v0.1.24. It remains useful as design/reference work, not established candidate provenance.
- Prior note of a passing CI artifact for 0.1.8 does not establish staging ZIP provenance or certify v0.1.24.

## Open UX and engineering gates
1. Contacts grid/table needs responsive design and actual browser checks; removing a hint did not solve wide-table scrolling.
2. Analytics table changes require verification against long real values and clipping at representative viewport widths.
3. Inspect routing and consent behavior for candidate 0.1.24 as independent runtime coverage. Historical user test reports are not fresh validation.
4. For the previously observed round-robin code, serialized state mutation and persistence verification require test evidence; do not assume current candidate inherits a different PR branch's lock implementation.
5. Correct `readme.txt` Stable tag before release.
6. Correlate exact staging plugin ZIP hash with candidate artifact before acceptance.

## Status
- Archive syntax/integrity: **CONFIRMED PASS**.
- Current GitHub metadata/PR head: **CONFIRMED**.
- Provenance link from installed 0.1.24 to PR #4/main: **NOT ESTABLISHED**.
- Runtime visual and functional acceptance: **PENDING**.
- Merge/release/production: **NOT AUTHORIZED**.

## Next action
Obtain a reliable source handoff of the exact 0.1.24 candidate into a review branch or compare complete archive tree to the intended GitHub source. Then apply the minimal responsive UX corrections, run CI and staging regression. Preserve PR #4 as draft until lineage and test coverage are reconciled.
