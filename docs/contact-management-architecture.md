# Contact Management and Routing Notes

## Contact records
Contacts are stored in the plugin-owned `{prefix}hatnch_contacts` table. Current routing considers active status and uses `sort_order` followed by record ID for round-robin sequencing. The `weight` column is retained for compatibility but is not used by the current routing resolver.

## Default Contact behavior
- Contact Management offers `Set as Default` for active contacts. The action is a nonce- and capability-protected POST.
- The selected active contact ID is saved to existing `hatnch_settings.default_contact`. Direct routing resolves this setting.
- Previous default remains active and analytics history is not changed by reassignment.
- Current Default Contact cannot be deactivated or permanently deleted. Choose another active Default first.
- Target status is validated at request time; the serialized settings option is not a transactional compare-and-swap primitive. Simultaneous admin updates/interleaved state changes require concurrency testing before release.

## Deletion and analytics
Permanent deletion runs only through the administrative POST action with a per-record nonce and capability check. Before deleting the contact, associated analytics events are anonymized by setting `contact_id` to NULL. The operation requires transactional table engines and is performed in a database transaction; it refuses or rolls back on detected failures. Historical event rows are retained.

## State-changing requests
Create/update, toggle and delete actions are handled through WordPress `admin-post.php` POST endpoints, with server-side capability and nonce validation. Read-only admin query arguments are not used to mutate contact state.

## Plugin Check transaction notes
Transaction control statements (`START TRANSACTION`, `ROLLBACK`, `COMMIT`) operate on connection transaction state and do not retrieve cacheable application data. PHPCS `NoCaching` exclusions for these statements are explicitly scoped and justified. Data-reading/writing statements continue to use the plugin's existing custom-table access strategy.

## Analytics attribution after permanent deletion

New click events store `contact_reference` alongside the live `contact_id`. The reference contains only the numeric contact ID and is not a name or phone number. On permanent contact deletion, matching events retain the reference while `contact_id` is set to `NULL`. Reports resolve the reference to the current contact name if that contact still exists; otherwise they render `Removed contact #ID`. If older events have already lost both the contact ID and reference, they remain `Unknown / removed`; the plugin does not infer their identity.

The 1.2.0 schema migration adds the nullable reference column and date index through dbDelta. Existing rows are not backfilled with guessed identity.

## Analytics administration

Administrators with `manage_options` can export aggregate CSV reports for 7, 30, 90, or 180 days. Exports exclude phone numbers, raw event IDs, and visitor identifiers. Analytics event deletion supports an inclusive date range in the WordPress site timezone and Delete All with typed `DELETE` confirmation. Deletion does not alter contacts, routing state, or plugin settings. Event retention remains 180 days.
