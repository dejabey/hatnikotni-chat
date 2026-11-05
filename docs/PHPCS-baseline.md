# WordPress Coding Standards baseline

## Snapshot

The full-repository PHPCS run in GitHub Actions Build #753 (5 November 2026) reported existing findings in files outside the 0.1.26 contact-status patch:

| File | Errors | Warnings |
|---|---:|---:|
| `includes/class-hatnch-analytics.php` | 10 | 10 |
| `includes/class-hatnch-routing.php` | 21 | 15 |
| `includes/class-hatnch-analytics-admin.php` | 8 | 9 |
| `includes/class-hatnch-admin.php` | 46 | 21 |
| `includes/class-hatnch-whatsapp.php` | 6 | 7 |
| `includes/class-hatnch-plugin.php` | 0 | 5 |
| **Total** | **91** | **67** |

These findings include formatting violations and substantive issues such as output escaping, nonce verification, sanitization, prepared SQL, and translator comments. They must not be silenced or mass-reformatted without reviewing the affected code and its behavior.

## Incremental enforcement

The validation workflow now runs PHPCS against changed production PHP files in each pull request. This prevents unrelated legacy findings from blocking a narrowly scoped patch while ensuring newly changed production PHP is checked.

This is an incremental gate, not a claim that the whole plugin currently passes WordPress Coding Standards. The full-repository backlog remains open and should be resolved in separately scoped, reviewed changes. Do not add broad PHPCS exclusions to hide these findings.
