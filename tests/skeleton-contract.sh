#!/usr/bin/env bash
set -euo pipefail

fail() {
  echo "FAIL: $1" >&2
  exit 1
}

for file in hatnikotni-chat.php uninstall.php includes/*.php; do
  [ -f "$file" ] || fail "Missing expected PHP file: $file"
  php -l "$file" >/dev/null || fail "PHP syntax error: $file"
done

grep -q "Text Domain: hatnikotni-chat" hatnikotni-chat.php || fail "Text domain mismatch"
grep -q "HKC_VERSION" hatnikotni-chat.php || fail "Version constant missing"
grep -q "\$wpdb->prefix . 'hkc_contacts'" includes/class-hkc-contacts.php || fail "Contacts table prefix contract missing"
grep -q "\$wpdb->prefix . 'hkc_events'" includes/class-hkc-analytics.php || fail "Events table prefix contract missing"
grep -q "get_charset_collate" includes/class-hkc-contacts.php || fail "Contacts charset contract missing"
grep -q "get_charset_collate" includes/class-hkc-analytics.php || fail "Events charset contract missing"
grep -q "dbDelta" includes/class-hkc-contacts.php || fail "Contacts dbDelta contract missing"
grep -q "dbDelta" includes/class-hkc-analytics.php || fail "Events dbDelta contract missing"
grep -q "WP_UNINSTALL_PLUGIN" uninstall.php || fail "Uninstall guard missing"

echo "PASS: Hatnikotni Chat skeleton contracts"
