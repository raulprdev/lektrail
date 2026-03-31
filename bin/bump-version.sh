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

# Update lektrail.php header
sed -i '' "s/^ \* Version:.*/ * Version:           $VERSION/" "$PLUGIN_DIR/lektrail.php"

# Update lektrail.php $version variable
sed -i '' "s/\$version = '[^']*';/\$version = '$VERSION';/" "$PLUGIN_DIR/lektrail.php"

# Update readme.txt Stable tag
sed -i '' "s/^Stable tag:.*/Stable tag: $VERSION/" "$PLUGIN_DIR/readme.txt"

# Update translation files
for file in "$PLUGIN_DIR"/languages/*.pot "$PLUGIN_DIR"/languages/*.po; do
    if [ -f "$file" ]; then
        sed -i '' "s/Project-Id-Version: LekTrail Reading Tracker [0-9.]*/Project-Id-Version: LekTrail Reading Tracker $VERSION/" "$file"
    fi
done

echo "Updated files:"
echo "  - lektrail.php (header and \$version)"
echo "  - readme.txt (Stable tag)"
echo "  - languages/*.pot and *.po (Project-Id-Version)"

echo ""
echo "Verify changes:"
grep -n "Version:" "$PLUGIN_DIR/lektrail.php" | head -1
grep -n "\$version = " "$PLUGIN_DIR/lektrail.php"
grep -n "Stable tag:" "$PLUGIN_DIR/readme.txt"
grep -n "Project-Id-Version:" "$PLUGIN_DIR/languages/lektrail.pot" | head -1

echo ""
echo "Next steps:"
echo "  git add -A && git commit -m \"Bump version to $VERSION\""
echo "  git tag v$VERSION"
echo "  git push origin main && git push origin v$VERSION"