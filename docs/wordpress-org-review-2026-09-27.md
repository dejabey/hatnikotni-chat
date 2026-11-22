# WordPress.org review email of 2026-09-27: requirements and status

Source: the owner's copy of the email "[WordPress Plugin Directory] Review in Progress: Hatnikotni Chat", sent by plugins@wordpress.org on 2026-09-27 14:09 UTC and supplied in chat on 2026-11-19. Only the technical content is recorded here; no personal addresses.

- **Review ID:** `AUTOPREREVIEW COM hatnikotni-chat/zaryl/27Sep26/T1 27Sep26/4.3 (P0TDX376260HGN)`
- **File reviewed:** `hatnikotni-chat-0.1.0.zip`. Slug: `hatnikotni-chat` (it cannot be changed after approval; keep it).
- **Status:** pended until 2026-11-22. **Update uploaded and reply sent by the owner on 2026-11-22, about 11:14 MYT** (owner-reported). The file is the 0.1.30 inner ZIP (expected `80E45498…`, from CI #942, as instructed). The reply text is `docs/wordpress-org-reply-draft.md`. Now waiting for the reviewer; do not ask for status before about 2026-12-22 (one month).
- **Deadline:** "If you believe there is a requirement you cannot accomplish and choose not to make changes, your plugin submission will be rejected after three months." From 2026-09-27, that is about **2026-12-27**.

## How to respond (from the email)
1. Fix everything and test thoroughly. A partial update can lead to rejection without a further review.
2. Upload the corrected ZIP on https://wordpress.org/plugins/developers/add/ while logged in as `zaryl`.
3. Reply to the **same email thread**; do not start a new email. Keep the reply short, do not list the changes (they re-review the whole plugin), and include only context that helps the review.
4. Avoid status requests unless a month or more has passed since replying.

## Issues raised and their status at 0.1.29/0.1.30

| # | Issue in the email | Status | Evidence |
|---|---|---|---|
| 1 | Prefixes of at least 4 characters for declarations, globals, stored data and WordPress declarations | Addressed | 2026-11-19 audit of 0.1.29 source: classes `HATNCH_*`; constants `HATNCH_*`; no unprefixed functions or namespaces; globals `$hatnch_*` (uninstall) and `$wpdb`; options `hatnch_settings`, `hatnch_db_version`, `hatnch_routing_state`, `hatnch_routing_failures`, `hatnch_rr_*`; hooks `hatnch_*`; `admin_post_hatnch_*` actions; cron `hatnch_daily_cleanup`; admin pages `hatnch*`; script/style handles `hatnch-*`; JS global `hatnchPrivacyConfig`; cookies `hatnch_*`; tables `{prefix}hatnch_*`; shortcode `hatnikotni_chat`. |
| 2 | Plugin URI https://github.com/dejabey/hatnikotni-chat returned 404 (private repository) | Addressed | The repository is public; HTTP 200 on 2026-11-19. |
| 3 | "Social media buttons" category: 500+ plugins; asks how the plugin stands out, and says it may be rejected if too similar without significant new value | **Open (judgement)** | Positioning is in `docs/wordpress-org-differentiation.md`. A current competitor comparison is still to be done before replying. |
| — | General: external services must be disclosed in the readme with terms/privacy links (Plugins Team review checklist) | Addressed in 0.1.30 | `== External services ==` section in `readme.txt` (contract-tested). |
| — | Plugin Check | Clean on 0.1.28 (owner-reported) and run again for 0.1.29 on staging (step 3 of "Ujian 0.1.29": PASS). | |

## Which ZIP to upload
Upload the CI artifact of the 0.1.30 commit (inner ZIP), after the same SHA-256 check as for staging. 0.1.30 has the same code as the staging-tested 0.1.29; only `readme.txt` and version strings differ.
