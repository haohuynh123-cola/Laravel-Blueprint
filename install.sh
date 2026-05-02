#!/usr/bin/env bash
#
# Laravel Blueprint installer.
#
#   curl -sSL https://raw.githubusercontent.com/haohuynh123-cola/Laravel-Blueprint/main/install.sh | bash
#
# Environment overrides:
#   INSTALL_DIR  Target directory for the binary  (default: /usr/local/bin)
#   VERSION      Specific tag to install          (default: latest)
#   GH_REPO      GitHub repo (owner/name)         (default: haohuynh123-cola/Laravel-Blueprint)

set -euo pipefail

INSTALL_DIR="${INSTALL_DIR:-/usr/local/bin}"
GH_REPO="${GH_REPO:-haohuynh123-cola/Laravel-Blueprint}"
VERSION="${VERSION:-latest}"
BINARY_NAME="blueprint"

bold() { printf '\033[1m%s\033[0m\n' "$*"; }
info() { printf '\033[36m▸\033[0m %s\n' "$*"; }
warn() { printf '\033[33m⚠\033[0m %s\n' "$*" >&2; }
fail() { printf '\033[31m✗\033[0m %s\n' "$*" >&2; exit 1; }

bold "Laravel Blueprint installer"

# 1. Verify PHP is available and recent enough.
if ! command -v php >/dev/null 2>&1; then
    fail "PHP is not installed. Install PHP 8.2+ first (e.g. brew install php)."
fi

PHP_VERSION="$(php -r 'echo PHP_VERSION;')"
PHP_MAJOR_MINOR="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
if [ "$(printf '%s\n8.2' "$PHP_MAJOR_MINOR" | sort -V | head -n1)" != "8.2" ]; then
    fail "PHP 8.2+ is required (found $PHP_VERSION)."
fi
info "PHP $PHP_VERSION ✓"

# 2. Resolve the download URL.
if [ "$VERSION" = "latest" ]; then
    info "Resolving latest release from $GH_REPO…"
    DOWNLOAD_URL="https://github.com/$GH_REPO/releases/latest/download/blueprint.phar"
else
    DOWNLOAD_URL="https://github.com/$GH_REPO/releases/download/$VERSION/blueprint.phar"
fi

# 3. Download to a tempfile, fail loudly if the asset is missing.
TMP_FILE="$(mktemp)"
trap 'rm -f "$TMP_FILE"' EXIT

info "Downloading $DOWNLOAD_URL"
HTTP_STATUS="$(curl -sSL -w '%{http_code}' -o "$TMP_FILE" "$DOWNLOAD_URL" || true)"
if [ "$HTTP_STATUS" != "200" ]; then
    fail "Download failed (HTTP $HTTP_STATUS). The release may not have a phar attached yet."
fi

# 4. Sanity check — the phar should at least look like a phar.
if ! head -c 100 "$TMP_FILE" | grep -q '^#!/usr/bin/env php'; then
    fail "Downloaded file does not look like a PHP archive."
fi

# 5. Install. Use sudo only if the target dir is not writable.
TARGET="$INSTALL_DIR/$BINARY_NAME"
if [ -w "$INSTALL_DIR" ]; then
    install -m 0755 "$TMP_FILE" "$TARGET"
else
    info "Installing to $TARGET (requires sudo)"
    sudo install -m 0755 "$TMP_FILE" "$TARGET"
fi

bold ""
bold "✓ Installed $BINARY_NAME → $TARGET"
bold ""
"$TARGET" --version || warn "Binary installed but failed to run — check that '$INSTALL_DIR' is on your PATH."
echo ""
info "Try it: blueprint new"
