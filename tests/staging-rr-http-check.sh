#!/usr/bin/env bash
# Hatnikotni Chat 0.1.26 — Round Robin staging test (sequential + concurrent).
# Never follows redirects: no WhatsApp page is opened and no message is sent.
# Prints only a 12-char SHA-256 prefix of the wa.me number, never the number itself.
# Usage: bash hatnch-rr-test.sh [SEQ_COUNT=4] [CONC_COUNT=12]
set -u
URL='https://staging.perlis.xyz/wp-admin/admin-post.php?action=hatnch_whatsapp_click'
SEQ=${1:-4}
CONC=${2:-12}
OUT=$(mktemp -d)

if command -v sha256sum >/dev/null 2>&1; then H='sha256sum'; else H='shasum -a 256'; fi

hit() { # $1 = label
  local hdr t0 code loc by num h
  hdr="$OUT/$1.hdr"
  t0=$(date -u +%H:%M:%S.%N 2>/dev/null | cut -c1-12)
  code=$(curl -s -o /dev/null -D "$hdr" -w '%{http_code}' --max-time 30 \
         -H 'Cache-Control: no-cache' "$URL&hatnch_t=$1-$RANDOM")
  loc=$(grep -i '^location:' "$hdr" | tr -d '\r' | cut -d' ' -f2-)
  by=$(grep -i '^x-redirect-by:' "$hdr" | tr -d '\r' | cut -d' ' -f2-)
  case "$loc" in
    https://wa.me/*) num=${loc#https://wa.me/}; num=${num%%\?*}
                     h=$(printf '%s' "$num" | $H | cut -c1-12) ;;
    *) h="NOT_WA:${loc}" ;;
  esac
  printf '%s\t%s\t%s\t%s\t%s\n' "$1" "$t0" "$code" "${by:-none}" "$h"
}

echo "== START $(date -u '+%Y-%m-%dT%H:%M:%SZ')"
echo -e "label\tstart_utc\thttp\tx_redirect_by\tphone_h12"
echo "-- sequential ($SEQ)"
for i in $(seq 1 "$SEQ"); do hit "s$i"; done
sleep 2
echo "-- concurrent ($CONC)"
for i in $(seq 1 "$CONC"); do hit "c$i" > "$OUT/c$i.row" & done
wait
for i in $(seq 1 "$CONC"); do cat "$OUT/c$i.row"; done | sort -k2
echo "== END $(date -u '+%Y-%m-%dT%H:%M:%SZ')"
echo "-- sample response headers (c1):"
tr -d '\r' < "$OUT/c1.hdr" | grep -iv '^set-cookie' | grep -iv '^location'
