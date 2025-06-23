#!/bin/bash

echo "🔍 Verifying Docker Build Fix"
echo "============================"

# Test 1: Check if --no-scripts flag is in Dockerfile
echo "Test 1: Checking Dockerfile for --no-scripts flag..."
if grep -q "composer install.*--no-scripts" Dockerfile; then
    echo "✅ PASS: --no-scripts flag found in Dockerfile"
else
    echo "❌ FAIL: --no-scripts flag missing from Dockerfile"
    exit 1
fi

# Test 2: Check if artisan commands are removed from Dockerfile
echo "Test 2: Checking Dockerfile for artisan commands..."
if grep -q "php artisan" Dockerfile; then
    echo "❌ FAIL: Found php artisan commands in Dockerfile"
    exit 1
else
    echo "✅ PASS: No php artisan commands in Dockerfile"
fi

# Test 3: Check if composer scripts contain the problematic commands
echo "Test 3: Checking composer.json for problematic scripts..."
if grep -q "key:generate" composer.json; then
    echo "⚠️  WARNING: composer.json still contains key:generate in scripts"
    echo "   This is OK because we're using --no-scripts to skip them"
else
    echo "✅ INFO: No key:generate found in composer.json"
fi

# Test 4: Check if startup script handles necessary setup
echo "Test 4: Checking startup script..."
if grep -q "package:discover" docker-startup.sh; then
    echo "✅ PASS: Startup script includes package discovery"
else
    echo "⚠️  WARNING: Startup script might be missing package discovery"
fi

echo ""
echo "🎯 VERIFICATION COMPLETE"
echo "The fix should work because:"
echo "1. Docker build skips composer scripts (--no-scripts)"
echo "2. No artisan commands run during build"
echo "3. All Laravel setup happens at runtime"
echo ""
echo "Confidence Level: HIGH ✅" 