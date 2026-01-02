#!/bin/bash
#
# Merge Conflict Auto-Resolution Script
# This script helps resolve merge conflicts by accepting incoming changes from copilot branch
#
# Usage: bash resolve-conflicts.sh

set -e

echo "========================================="
echo "Merge Conflict Resolution Helper"
echo "========================================="
echo ""

# Check if we're in a merge conflict state
if ! git status | grep -q "both modified"; then
    echo "✓ No merge conflicts detected"
    exit 0
fi

echo "Detected merge conflicts. Resolving..."
echo ""

# List conflicted files
echo "Conflicted files:"
git status --short | grep "^UU"
echo ""

# Resolve common conflicted files by accepting incoming changes
CONFLICTED_FILES=("views/home.php" "views/layouts/main.php")

for file in "${CONFLICTED_FILES[@]}"; do
    if git status --short | grep -q "UU $file"; then
        echo "Resolving: $file (accepting incoming changes)"
        git checkout --theirs "$file"
        git add "$file"
        echo "  ✓ Resolved: $file"
    fi
done

echo ""
echo "========================================="
echo "Resolution Summary"
echo "========================================="

# Check remaining conflicts
REMAINING=$(git status --short | grep "^UU" | wc -l)

if [ "$REMAINING" -eq 0 ]; then
    echo "✓ All conflicts resolved!"
    echo ""
    echo "Next steps:"
    echo "1. Review the resolved files:"
    echo "   git diff --cached"
    echo ""
    echo "2. Commit the merge:"
    echo "   git commit -m 'Merge copilot/fix-user-ui-ux-issues'"
    echo ""
    echo "3. Test your application to ensure everything works"
else
    echo "⚠ $REMAINING conflicts remain. Please resolve manually:"
    git status --short | grep "^UU"
    echo ""
    echo "Manual resolution steps:"
    echo "1. Open each file in your editor"
    echo "2. Look for conflict markers (<<<<<<< HEAD)"
    echo "3. Choose the correct version or merge both"
    echo "4. Remove conflict markers"
    echo "5. Run: git add <file>"
    echo "6. Run: git commit"
fi

echo ""
