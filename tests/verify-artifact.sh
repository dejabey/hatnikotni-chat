#!/usr/bin/env bash
# Verify a downloaded Hatnikotni Chat CI artifact before installing it.
#
# Usage (Git Bash on Windows, or any Bash):
#   bash tests/verify-artifact.sh <downloaded-artifact.zip> <expected-sha256>
#
# Example:
#   bash tests/verify-artifact.sh ~/Downloads/hatnikotni-chat-0.1.29.zip \
#     f85ad689b589c51ddf055de400e189faade95689b5c0724b44c74e1fab1989a7
#
# Checks the outer artifact against the digest recorded in PROJECT-HANDOFF.md,
# extracts the inner installable ZIP next to it, prints the inner ZIP's
# SHA-256 (report it for the handoff) and the plugin version it contains.
# Upload the INNER ZIP to WordPress, never the outer artifact.
set -euo pipefail

artifact="${1:-}"
expected="${2:-}"
if [ -z "$artifact" ] || [ -z "$expected" ] || [ ! -f "$artifact" ]; then
	echo "Usage: bash tests/verify-artifact.sh <artifact.zip> <expected-sha256>" >&2
	exit 2
fi

expected="$(printf '%s' "${expected#sha256:}" | tr 'A-F' 'a-f')"
actual="$(sha256sum "$artifact" | awk '{print $1}')"

echo "Outer artifact : $artifact"
echo "Expected       : $expected"
echo "Actual         : $actual"
if [ "$actual" != "$expected" ]; then
	echo "FAIL: SHA-256 does not match. Do not install this file." >&2
	exit 1
fi
echo "OK: outer artifact matches the CI digest."

workdir="$(mktemp -d)"
trap 'rm -rf "$workdir"' EXIT
unzip -q "$artifact" -d "$workdir"
inner_count="$(find "$workdir" -maxdepth 1 -name '*.zip' | wc -l | tr -d ' ')"
if [ "$inner_count" != "1" ]; then
	echo "FAIL: expected exactly one inner ZIP, found $inner_count." >&2
	exit 1
fi
inner="$(find "$workdir" -maxdepth 1 -name '*.zip')"
out_dir="$(cd "$(dirname "$artifact")" && pwd)/inner"
mkdir -p "$out_dir"
cp "$inner" "$out_dir/"
inner_copy="$out_dir/$(basename "$inner")"

version="$(unzip -p "$inner_copy" hatnikotni-chat/hatnikotni-chat.php | sed -n 's/^ \* Version: //p' | tr -d '\r')"
echo "Inner ZIP      : $inner_copy"
echo "Inner SHA-256  : $(sha256sum "$inner_copy" | awk '{print toupper($1)}')"
echo "Plugin version : $version"
echo "Upload the inner ZIP above in WordPress (Plugins > Add New > Upload Plugin)."
