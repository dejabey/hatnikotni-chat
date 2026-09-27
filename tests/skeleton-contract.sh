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
grep -q "HATC_VERSION" hatnikotni-chat.php || fail "Version constant missing"
grep -q "class-hatc-privacy.php" hatnikotni-chat.php || fail "Privacy module load missing"
grep -q "hatc_has_analytics_consent" includes/class-hatc-privacy.php || fail "Analytics consent filter missing"
grep -q "wp_add_privacy_policy_content" includes/class-hatc-privacy.php || fail "Privacy policy integration missing"
grep -q "\$wpdb->prefix . 'hatc_contacts'" includes/class-hatc-contacts.php || fail "Contacts table prefix contract missing"
grep -q "\$wpdb->prefix . 'hatc_events'" includes/class-hatc-analytics.php || fail "Events table prefix contract missing"
grep -q "get_charset_collate" includes/class-hatc-contacts.php || fail "Contacts charset contract missing"
grep -q "get_charset_collate" includes/class-hatc-analytics.php || fail "Events charset contract missing"
grep -q "dbDelta" includes/class-hatc-contacts.php || fail "Contacts dbDelta contract missing"
grep -q "dbDelta" includes/class-hatc-analytics.php || fail "Events dbDelta contract missing"
grep -q "WP_UNINSTALL_PLUGIN" uninstall.php || fail "Uninstall guard missing"
grep -q "hatc_routing_state" uninstall.php || fail "Routing state uninstall cleanup missing"

grep -q "function save" includes/class-hatc-contacts.php || fail "Contact save contract missing"
grep -q "function set_status" includes/class-hatc-contacts.php || fail "Contact status contract missing"
grep -q "admin_post_hatc_save_contact" includes/class-hatc-admin.php || fail "Contact save admin action missing"
grep -q "check_admin_referer" includes/class-hatc-admin.php || fail "Admin nonce contract missing"
grep -q "current_user_can( 'manage_options' )" includes/class-hatc-admin.php || fail "Admin capability contract missing"
grep -q "wp_safe_redirect" includes/class-hatc-admin.php || fail "Safe redirect contract missing"
grep -q "maybe_upgrade" includes/class-hatc-plugin.php || fail "Database upgrade contract missing"
grep -q "hatc_save_settings" includes/class-hatc-admin.php || fail "Settings save action missing"
grep -q "routing_method" includes/class-hatc-admin.php || fail "Routing settings contract missing"

grep -q "case 'direct'" includes/class-hatc-routing.php || fail "Direct routing contract missing"
grep -q "case 'random'" includes/class-hatc-routing.php || fail "Random routing contract missing"
grep -q "case 'round_robin'" includes/class-hatc-routing.php || fail "Round-robin routing contract missing"
grep -q "get_active" includes/class-hatc-routing.php || fail "Active-contact routing contract missing"
grep -q "hatc_routing_state" includes/class-hatc-routing.php || fail "Round-robin state contract missing"

grep -q "admin_post_nopriv_hatc_whatsapp_click" includes/class-hatc-whatsapp.php || fail "Public WhatsApp action contract missing"
grep -q "record_click" includes/class-hatc-whatsapp.php || fail "Analytics action contract missing"
grep -q "https://wa.me/" includes/class-hatc-whatsapp.php || fail "WhatsApp URL contract missing"
grep -q "setcookie" includes/class-hatc-campaign.php || fail "Campaign cookie contract missing"
grep -q "hatc_campaign" includes/class-hatc-campaign.php || fail "Campaign cookie name contract missing"
grep -q "has_analytics_consent" includes/class-hatc-campaign.php || fail "Campaign consent gate missing"
grep -q "has_analytics_consent" includes/class-hatc-analytics.php || fail "Analytics consent gate missing"
grep -q "whatsapp_click" includes/class-hatc-analytics.php || fail "Analytics event contract missing"
grep -q "get_summary" includes/class-hatc-analytics.php || fail "Analytics summary contract missing"
grep -q "function cleanup" includes/class-hatc-analytics.php || fail "Analytics cleanup contract missing"
grep -q "hatc_daily_cleanup" includes/class-hatc-plugin.php || fail "Analytics cleanup schedule contract missing"
grep -q "hatc-analytics" includes/class-hatc-analytics-admin.php || fail "Analytics admin contract missing"
grep -q "hatc-inline" includes/class-hatc-shortcode.php || fail "Shortcode rendering contract missing"
grep -q "=== Hatnikotni Chat ===" readme.txt || fail "WordPress.org readme missing"
grep -q "Stable tag:" readme.txt || fail "WordPress.org stable tag missing"

echo "PASS: Hatnikotni Chat contracts"
