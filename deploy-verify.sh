#!/bin/bash

# Deployment Verification Script for EN NUR Membership System
# This script verifies that the deployment was successful

echo "🚀 Starting Deployment Verification..."
echo "=================================="

# Configuration
APP_URL="https://en-nur-membership.onrender.com"
MAX_RETRIES=10
RETRY_DELAY=30

# Function to check HTTP response
check_endpoint() {
    local url=$1
    local expected_status=$2
    local description=$3
    
    echo "Checking $description..."
    response=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    
    if [ "$response" = "$expected_status" ]; then
        echo "✅ $description: OK ($response)"
        return 0
    else
        echo "❌ $description: FAILED ($response)"
        return 1
    fi
}

# Function to wait for service to be ready
wait_for_service() {
    echo "⏳ Waiting for service to be ready..."
    
    for i in $(seq 1 $MAX_RETRIES); do
        echo "Attempt $i/$MAX_RETRIES..."
        
        if check_endpoint "$APP_URL/health" "200" "Basic Health Check"; then
            echo "✅ Service is ready!"
            return 0
        fi
        
        if [ $i -lt $MAX_RETRIES ]; then
            echo "⏳ Waiting ${RETRY_DELAY}s before retry..."
            sleep $RETRY_DELAY
        fi
    done
    
    echo "❌ Service failed to become ready after $MAX_RETRIES attempts"
    return 1
}

# Main verification process
echo "1. Waiting for service to become available..."
if ! wait_for_service; then
    echo "💥 DEPLOYMENT FAILED: Service not available"
    exit 1
fi

echo ""
echo "2. Running comprehensive health checks..."

# Test all critical endpoints
FAILED_CHECKS=0

# Basic endpoints
check_endpoint "$APP_URL" "200" "Homepage" || ((FAILED_CHECKS++))
check_endpoint "$APP_URL/health" "200" "Health Check" || ((FAILED_CHECKS++))
check_endpoint "$APP_URL/health/detailed" "200" "Detailed Health Check" || ((FAILED_CHECKS++))

# Application endpoints
check_endpoint "$APP_URL/debug-info" "200" "Debug Info" || ((FAILED_CHECKS++))
check_endpoint "$APP_URL/test-route" "200" "Test Route" || ((FAILED_CHECKS++))

# Test detailed health check response
echo ""
echo "3. Analyzing detailed health check..."
health_response=$(curl -s "$APP_URL/health/detailed")
echo "Health Check Response:"
echo "$health_response" | python3 -m json.tool 2>/dev/null || echo "$health_response"

# Check if health response indicates any issues
if echo "$health_response" | grep -q '"status":"ERROR"'; then
    echo "❌ Health check indicates system errors"
    ((FAILED_CHECKS++))
fi

echo ""
echo "=================================="
echo "📊 DEPLOYMENT VERIFICATION SUMMARY"
echo "=================================="

if [ $FAILED_CHECKS -eq 0 ]; then
    echo "🎉 SUCCESS: All checks passed!"
    echo "✅ Application is fully deployed and functional"
    echo "🌐 Access your application at: $APP_URL"
    echo ""
    echo "Next steps:"
    echo "- Configure payment providers (Stripe/PayPal) in Render dashboard"
    echo "- Set up email service for notifications"
    echo "- Create admin user account"
    exit 0
else
    echo "💥 FAILURE: $FAILED_CHECKS check(s) failed"
    echo "❌ Deployment requires attention"
    echo ""
    echo "Troubleshooting:"
    echo "- Check Render build logs for errors"
    echo "- Verify database connection"
    echo "- Check environment variables"
    exit 1
fi 