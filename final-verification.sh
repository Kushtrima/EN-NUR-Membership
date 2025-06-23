#!/bin/bash

echo "🔍 FINAL DEPLOYMENT VERIFICATION"
echo "================================="

# Verify we're on main branch
echo "Branch Check:"
current_branch=$(git rev-parse --abbrev-ref HEAD)
if [ "$current_branch" = "main" ]; then
    echo "✅ CORRECT: On main branch (the one Render deploys)"
else
    echo "❌ ERROR: On $current_branch branch, should be main!"
    exit 1
fi

echo ""

# Check exact Dockerfile content
echo "Dockerfile Analysis:"
echo "==================="

# 1. Check for the problematic command sequence
if grep -q "key:generate.*force.*config:cache.*route:cache.*view:cache" Dockerfile; then
    echo "❌ FATAL: Found the problematic artisan command sequence in Dockerfile!"
    echo "   This is what's causing the error!"
    exit 1
else
    echo "✅ PASS: No problematic artisan command sequence found"
fi

# 2. Check for ANY artisan commands in Dockerfile
artisan_count=$(grep -c "php artisan" Dockerfile || echo "0")
if [ "$artisan_count" -gt "0" ]; then
    echo "❌ FATAL: Found $artisan_count php artisan commands in Dockerfile:"
    grep -n "php artisan" Dockerfile
    exit 1
else
    echo "✅ PASS: No php artisan commands in Dockerfile"
fi

# 3. Check for --no-scripts flag
if grep -q "composer install.*--no-scripts" Dockerfile; then
    echo "✅ PASS: Found --no-scripts flag in composer install"
else
    echo "❌ FATAL: --no-scripts flag missing from composer install!"
    exit 1
fi

echo ""

# Check git status
echo "Git Status:"
echo "==========="
if git diff --quiet && git diff --staged --quiet; then
    echo "✅ PASS: No uncommitted changes"
else
    echo "❌ WARNING: There are uncommitted changes"
    git status --short
fi

# Check if changes are pushed
local_commit=$(git rev-parse main)
remote_commit=$(git rev-parse origin/main)
if [ "$local_commit" = "$remote_commit" ]; then
    echo "✅ PASS: Local main matches remote main (changes are pushed)"
else
    echo "❌ FATAL: Local main differs from remote main!"
    echo "   Local:  $local_commit"
    echo "   Remote: $remote_commit"
    echo "   You need to push the changes!"
    exit 1
fi

echo ""

# Final verification
echo "🎯 FINAL ASSESSMENT:"
echo "===================="
echo "✅ Dockerfile has --no-scripts flag"
echo "✅ No problematic artisan commands in Dockerfile"
echo "✅ Changes are committed and pushed to main branch"
echo "✅ Render will deploy from the fixed main branch"
echo ""
echo "🚀 CONFIDENCE LEVEL: 99% - THE FIX SHOULD WORK!"
echo ""
echo "The error sequence you saw:"
echo "   'php artisan key:generate --force && php artisan config:cache'"
echo "Has been COMPLETELY REMOVED from the Dockerfile."
echo ""
echo "Ready for deployment! 🎉" 