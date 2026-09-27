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
grep -q "HATNCH_VERSION" hatnikotni-chat.php || fail "Version constant missing"
grep -q "class-hkc-privacy.php" hatnikotni-chat.php || fail "Privacy module load missing"
grep -q "hatnch_has_analytics_consent" includes/class-hkc-privacy.php || fail "Analytics consent filter missing"
grep -q "wp_add_privacy_policy_content" includes/class-hkc-privacy.php || fail "Privacy policy integration missing"
grep -q "\$wpdb->prefix . 'hatnch_contacts'" includes/class-hkc-contacts.php || fail "Contacts table prefix contract missing"
grep -q "\$wpdb->prefix . 'hatnch_events'" includes/class-hatnch-analytics.php || fail "Events table prefix contract missing"
grep -q "get_charset_collate" includes/class-hkc-contacts.php || fail "Contacts charset contract missing"
grep -q "get_charset_collate" includes/class-hatnch-analytics.php || fail "Events charset contract missing"
grep -q "dbDelta" includes/class-hkc-contacts.php || fail "Contacts dbDelta contract missing"
grep -q "dbDelta" includes/class-hatnch-analytics.php || fail "Events dbDelta contract missing"
grep -q "WP_UNINSTALL_PLUGIN" uninstall.php || fail "Uninstall guard missing"
grep -q "hatnch_routing_state" uninstall.php || fail "Routing state uninstall cleanup missing"

grep -q "function save" includes/class-hkc-contacts.php || fail "Contact save contract missing"
grep -q "function set_status" includes/class-hkc-contacts.php || fail "Contact status contract missing"
grep -q "admin_post_hatnch_save_contact" includes/class-hkc-admin.php || fail "Contact save admin action missing"
grep -q "check_admin_referer" includes/class-hkc-admin.php || fail "Admin nonce contract missing"
grep -q "current_user_can( 'manage_options' )" includes/class-hkc-admin.php || fail "Admin capability contract missing"
grep -q "wp_safe_redirect" includes/class-hkc-admin.php || fail "Safe redirect contract missing"
grep -q "maybe_upgrade" includes/class-hkc-plugin.php || fail "Database upgrade contract missing"
grep -q "hatnch_save_settings" includes/class-hkc-admin.php || fail "Settings save action missing"
grep -q "routing_method" includes/class-hkc-admin.php || fail "Routing settings contract missing"

grep -q "case 'direct'" includes/class-hkc-routing.php || fail "Direct routing contract missing"
grep -q "case 'random'" includes/class-hkc-routing.php || fail "Random routing contract missing"
grep -q "case 'round_robin'" includes/class-hkc-routing.php || fail "Round-robin routing contract missing"
grep -q "get_active" includes/class-hkc-routing.php || fail "Active-contact routing contract missing"
grep -q "hatnch_routing_state" includes/class-hkc-routing.php || fail "Round-robin state contract missing"

grep -q "admin_post_nopriv_hatnch_whatsapp_click" includes/class-hkc-whatsapp.php || fail "Public WhatsApp action contract missing"
grep -q "record_click" includes/class-hkc-whatsapp.php || fail "Analytics action contract missing"
grep -q "https://wa.me/" includes/class-hkc-whatsapp.php || fail "WhatsApp URL contract missing"
grep -q "setcookie" includes/class-hkc-campaign.php || fail "Campaign cookie contract missing"
grep -q "hatnch_campaign" includes/class-hkc-campaign.php || fail "Campaign cookie name contract missing"
grep -q "has_analytics_consent" includes/class-hkc-campaign.php || fail "Campaign consent gate missing"
grep -q "has_analytics_consent" includes/class-hatnch-analytics.php || fail "Analytics consent gate missing"
grep -q "whatsapp_click" includes/class-hatnch-analytics.php || fail "Analytics event contract missing"
grep -q "get_summary" includes/class-hatnch-analytics.php || fail "Analytics summary contract missing"
grep -q "function cleanup" includes/class-hatnch-analytics.php || fail "Analytics cleanup contract missing"
grep -q "hatnch_daily_cleanup" includes/class-hkc-plugin.php || fail "Analytics cleanup schedule contract missing"
grep -q "hatnch-analytics" includes/class-hatnch-analytics-admin.php || fail "Analytics admin contract missing"
grep -q "hatnch-inline" includes/class-hkc-shortcode.php || fail "Shortcode rendering contract missing"
grep -q "=== Hatnikotni Chat ===" readme.txt || fail "WordPress.org readme missing"
grep -q "Stable tag:" readme.txt || fail "WordPress.org stable tag missing"

echo "PASS: Hatnikotni Chat contracts"
