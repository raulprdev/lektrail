#!/bin/bash

set -e

if [ -z "$1" ]; then
    echo "Usage: bin/bump-version.sh <version>"
    echo "Example: bin/bump-version.sh 1.1.0"
    exit 1
fi

VERSION="$1"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$(dirname "$SCRIPT_DIR")"

echo "Bumping version to $VERSION..."

# Update completionist.php header
sed -i '' "s/^ \* Version:.*/ * Version:           $VERSION/" "$PLUGIN_DIR/completionist.php"

# Update completionist.php $version variable
sed -i '' "s/\$version = '[^']*';/\$version = '$VERSION';/" "$PLUGIN_DIR/completionist.php"

# Update readme.txt Stable tag
sed -i '' "s/^Stable tag:.*/Stable tag: $VERSION/" "$PLUGIN_DIR/readme.txt"

echo "Updated files:"
echo "  - completionist.php (header and \$version)"
echo "  - readme.txt (Stable tag)"

echo ""
echo "Verify changes:"
grep -n "Version:" "$PLUGIN_DIR/completionist.php" | head -1
grep -n "\$version = " "$PLUGIN_DIR/completionist.php"
grep -n "Stable tag:" "$PLUGIN_DIR/readme.txt"

echo ""
echo "Next steps:"
echo "  git add -A && git commit -m \"Bump version to $VERSION\""
echo "  git tag v$VERSION"
echo "  git push origin main && git push origin v$VERSION"