# Claude Instructions — Hatnikotni Chat

These instructions govern all Claude-assisted work in this repository. They are mandatory unless the user explicitly changes a rule. The repository is the source of truth; do not rely on prior chat history when the relevant facts can be checked here.

## 1. Read before doing any work

At the start of every task, read these files in order:

1. `AI-DEVELOPMENT-PROTOCOL.md` — development workflow contract.
2. `PROJECT-HANDOFF.md` — authoritative current project state and active decisions.
3. The latest relevant dated handoff under `docs/`, especially `docs/CLAUDE-CONTINUATION-HANDOFF-2026-11-17.md` for the 0.1.26 Round Robin work.
4. `README.md`, `readme.txt`, `CHANGELOG.md`, and relevant architecture/test documents when the task touches their subject.
5. The relevant source and tests before proposing or changing implementation.

If documents conflict, prefer the newest explicitly dated current-state section in `PROJECT-HANDOFF.md` for status, and the more specific technical document for implementation details. Do not silently reconcile contradictions: record the discrepancy and verify against GitHub history/source.

## 2. Project identity and technical baseline

- Plugin: **Hatnikotni Chat**, a standalone WordPress plugin.
- Repository: https://github.com/dejabey/hatnikotni-chat
- Current work: `main` = **0.1.28** (PR #8 merged 2026-11-19). Next version **0.1.29** on branch `dev/0.1.29`. See `PROJECT-HANDOFF.md` (top section). The 0.1.26 notes in §8 below are historical.
- It provides contact management and WhatsApp routing, including direct, random and Round Robin modes, plus consent-gated click analytics/campaign attribution and the `[hatnikotni_chat]` shortcode. Confirm current implementation in source; this summary is not a substitute for code inspection.
- Respect the declared minimum WordPress and PHP versions in the plugin header/readme. Do not introduce APIs or syntax newer than the declared minimum without an intentional, documented compatibility decision.
- Follow WordPress Plugin Handbook practices: capability checks, nonces, input validation/sanitization, output escaping, prepared SQL, internationalization, privacy-by-design, and least privilege.

## 3. GitHub is the only source of truth

- Make source, test, and documentation changes in GitHub branches and commits.
- Never edit plugin source directly on staging or production.
- Work on the explicitly active development branch or a new task-specific branch created from the correct base. Inspect branch/PR state before writing.
- Keep changes focused and reviewable. Avoid unrelated refactors, mass formatting, broad suppressions, and opportunistic scope expansion.
- Never force-push, rewrite history, retarget a PR, close a PR, or change branch protection without explicit user approval.
- Do not merge any PR without explicit user approval, even if CI is green or GitHub says it is mergeable.
- Do not publish a release, tag a release, or deploy to production without separate explicit approval.
- Do not generate or distribute an install ZIP as an approved release unless its source commit and CI artifact provenance are identified and verified.
- Before editing an existing file, fetch its current content and blob SHA. Use the current SHA for updates. Do not make concurrent writes to the same path.
- After every write, verify the resulting commit, branch head, changed files, and PR state. Never claim a write succeeded without tool confirmation.

## 4. Project-specific approval and safety boundaries

These are hard constraints:

- PR #4 and PR #5 must not be merged. PRs #1–#7 were closed unmerged on 2026-11-19 (superseded by #8); do not reopen or merge them without explicit approval.
- No PR may be merged without explicit user approval. PR #8 (0.1.28) was merged with owner approval on 2026-11-19. Future work goes through `dev/<version>` branches and PRs to `main`.
- PR #7 is a Draft candidate; do not mark it ready for review or merge it without approval.
- Do not deploy the unofficial 0.1.26 cache-isolation prototype.
- Do not deploy to production or publish to WordPress.org without explicit approval.
- Do not modify staging unless the user has authorized the specific action. Authorization to inspect or test one behavior is not blanket permission to deploy, alter settings/data, or perform cleanup.
- Do not reset the staging Round Robin cursor.
- Do not clear, truncate, rotate, or overwrite `debug.log` as a debugging shortcut.
- Do not send real WhatsApp messages during tests. Use the routing endpoint only to observe its redirect behavior.
- Do not uninstall/reinstall the plugin as a debugging shortcut; uninstall may delete plugin data.
- Do not change contacts, routing settings, options, database rows, cache configuration, or site configuration unless the exact mutation is necessary and explicitly authorized.
- Preserve existing state and logs. Prefer read-only inspection and disposable test environments.
- If a safe test cannot be performed with available tools, state the limitation and mark it inconclusive. Never fabricate evidence or overstate results.

## 5. Implementation standards

### WordPress/PHP
- Use WordPress APIs where appropriate; avoid direct filesystem or database shortcuts when a supported WordPress API exists.
- Check capabilities before privileged actions and verify nonces for state-changing requests.
- Validate identifiers and enumerated values; sanitize at trust boundaries; escape at output.
- Use `$wpdb->prepare()` for variable SQL values and safe identifier handling. Check database return values and distinguish `false`, zero affected rows, and missing records correctly.
- Consider concurrency, retries, stale object/option caches, transaction boundaries, and partial failures for persistence code.
- Use explicit, meaningful error handling. Do not silently swallow database, lock, HTTP, or filesystem failures.
- Always release acquired locks in guaranteed cleanup paths (for example, `finally`) and record actionable failure signals without leaking secrets or personal data.
- Do not assume MySQL/MariaDB lock or SQL behavior without checking compatibility with the supported server versions and target hosting.
- Preserve backward compatibility for stored options, database schema, hooks, filters, shortcodes, and existing user data unless a migration is explicitly designed and tested.

### Security and privacy
- Treat all request data, stored values, external responses, and imported content as untrusted.
- Keep analytics and campaign attribution subject to the plugin's consent model. WhatsApp routing must remain independent of analytics consent unless a documented product decision says otherwise.
- Do not log phone numbers, tokens, cookies, consent identifiers, or other personal/sensitive data.
- Do not add third-party trackers, remote dependencies, or external runtime services without explicit product/security review.
- Keep admin endpoints protected and return correct HTTP status codes for authorization failures.
- Consider CSRF, XSS, SQL injection, IDOR, open redirects, privilege escalation, information disclosure, and race conditions in every relevant change.

### Maintainability and compatibility
- Use the existing naming conventions, text domain, file structure, and established patterns.
- All user-facing strings must be internationalized using the existing text domain; do not hardcode translated strings in runtime logic.
- Keep English as the source language where the current project conventions require it; update translation source/catalogues and package compilation as appropriate.
- Avoid TODO/FIXME/XXX markers, temporary QA logging, debug output, and dead code in release candidates.
- Do not add blanket PHPCS suppressions to make CI green. Fix the underlying issue or document a narrowly justified exception.

## 6. Required development workflow

For every material change, follow this sequence:

1. **Establish state:** inspect current branch, HEAD, PR, worktree/diff if available, latest CI, and relevant documentation.
2. **Reproduce/understand:** trace the actual execution path and identify the root cause. Separate observed facts from hypotheses.
3. **Plan:** define scope, expected behavior, compatibility impact, failure modes, and tests before editing.
4. **Implement:** make the smallest robust change on the development branch.
5. **Test locally/contractually:** add or strengthen regression tests for the bug and important edge cases.
6. **Run CI:** inspect the workflow runs for the exact resulting HEAD, not merely an earlier commit. Review all required jobs and artifacts.
7. **Review diff:** inspect the complete diff for regressions, security, coding standards, unintended files, generated files, and version consistency.
8. **Document:** update applicable changelog, readme, architecture notes, test plan, and handoff. If a document does not need updating, record why in the handoff.
9. **Report:** state exact commit/HEAD, PR state, CI evidence, artifact provenance, runtime status, outstanding risks, and next action.
10. **Stop at approval gates:** do not merge, release, or deploy without the user's explicit approval.

Do not call work complete merely because code was written or a test command returned zero. The development lifecycle is:

`SOURCE → TEST/CONTRACT → BUILD/CI → DOCUMENTATION → CURRENT HANDOFF → RUNTIME STATUS`

## 7. Testing and evidence rules

Always distinguish these evidence levels:

- **Source inspection:** code appears to implement the intended behavior; not runtime proof.
- **Syntax/static checks:** syntax and static analysis passed; not functional proof.
- **Automated contract/unit tests:** the tested contract passed within the stated harness; not necessarily real WordPress/MySQL or browser proof.
- **Integration tests:** actual integration boundary tested in the documented environment.
- **Staging/runtime acceptance:** behavior verified on the current staging installation.
- **Production acceptance:** behavior verified on production after an approved deployment.

Use only the runtime labels defined in `AI-DEVELOPMENT-PROTOCOL.md`: **CONFIRMED**, **PENDING**, **FAILED**, **NOT APPLICABLE**.

For every reported test, record:
- exact source commit and plugin version;
- environment (WordPress, PHP, database/server where relevant);
- test method and tools/limitations;
- preconditions and state before/after;
- number of requests/iterations and actual observations;
- logs/errors and timestamps;
- what the test does not prove.

A helper that follows redirects or hides response headers cannot prove that the original WordPress handler ran. For concurrency tests, establish that every request reached the handler, capture response status/headers and correlate selected records with persisted state. If this cannot be established, report **INCONCLUSIVE**, not PASS.

Never claim the staging ZIP matches a CI artifact without hashing the installed ZIP and comparing it with the artifact digest. A successful CI package build does not prove what is installed on staging.

## 8. Current 0.1.26 Round Robin work — special instructions

For the active 0.1.26 candidate, consult the dated continuation handoff for full detail. Key constraints:

- PR #7 is OPEN / DRAFT / NOT MERGED. PR #6 is also OPEN / DRAFT / NOT MERGED.
- The runtime candidate code commit recorded in the handoff is `25d0d40aa6b0d1ae3749dad5014a33d94932dab5`. Documentation-only commits may move the branch HEAD; always inspect the live PR/branch before relying on a recorded SHA.
- CI #842 and #843 passed for that runtime candidate commit. This does not prove staging behavior.
- The user reported installing 0.1.26 on staging, but the dated handoff marks independent verification and artifact provenance as pending. Re-check; do not assume it is confirmed.
- Verify WPVibe quota before using it. The last recorded quota was stale; do not spend any banked reset without explicit approval.
- Read current plugin version, active contacts, routing method, cursor, cache configuration and log tail before testing. Preserve cursor and logs.
- Run sequential Round Robin tests first, then genuine concurrent requests using a method that proves handler execution and captures status/Location headers.
- Do not send WhatsApp messages. The endpoint should only redirect.
- If the tool cannot reliably observe the routing handler, report the test as inconclusive and propose a safe alternative; do not use redirect-following page HTML as proof.
- Record the cursor before and after, selected contact IDs, active contact set, request count, cache state and relevant log evidence.
- No staging deployment, PR merge, production release, or destructive cleanup is implied by permission to run tests.

The handoff contains detailed implementation notes for the advisory-lock/upsert persistence change and contact-status result handling. Inspect current source before modifying either.

## 9. CI and packaging discipline

- Inspect the workflow definitions to understand which jobs are required and what each proves.
- Confirm all required jobs passed for the exact candidate HEAD. A green run for an earlier SHA is not sufficient.
- Review package contents; ensure development tests, temporary files, credentials, local configuration, and unrelated files are excluded.
- Verify plugin header version, readme stable tag/version requirements, changelog and package name are consistent.
- Record artifact name, artifact ID, source commit, digest and workflow run URL.
- Treat artifacts as candidates until the user approves release/deployment.
- Do not manually patch a generated ZIP or package from a dirty/unreviewed worktree.

## 10. Documentation and handoff requirements

Keep the repository usable by a developer who has no access to the chat history.

For material changes, update as applicable:
- `PROJECT-HANDOFF.md` — current status, decisions, evidence and next action;
- `CHANGELOG.md` — user-visible/plugin changes;
- `README.md` and `readme.txt` — usage, compatibility, installation and release-facing details;
- architecture documentation — design, data flow, state and failure handling;
- tests and staging test plan — reproducible validation;
- dated continuation handoff — active work, verified facts, pending items and safety constraints.

Keep historical records intact. Add a new dated checkpoint rather than silently rewriting old evidence. Correct outdated current-state summaries explicitly and explain the correction.

## 11. Required response format to the user

Be direct, technically precise and candid. Do not agree reflexively; explain when an approach is unsafe, unsupported, or unrealistic.

For each material progress report, include:

1. **What changed** — exact files/commits and whether source or docs changed.
2. **Evidence** — exact CI run(s), test outcome and what they prove.
3. **Current state** — branch, PR state, version and runtime acceptance label.
4. **Limitations/risks** — unverified claims, test gaps and environment constraints.
5. **Next action** — one concrete next step.

Do not say “done”, “passed”, “deployed”, “verified”, or “ready” unless the corresponding evidence exists. Clearly label user-reported information separately from tool-verified information.

## 12. Final principle

Protect user data and production stability over speed. Prefer a small, testable, documented change over a broad rewrite. If an action crosses an approval boundary or risks irreversible effects, stop and ask for explicit approval.
