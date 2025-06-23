#!/bin/bash

# Laravel Render Debug Agent
# Terminal-based debugging tool for EN NUR Membership App

APP_URL="https://en-nur-membership.onrender.com"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

echo "🔍 Laravel Render Debug Agent - $TIMESTAMP"
echo "================================================"

# Function to test endpoint
test_endpoint() {
    local endpoint=$1
    local description=$2
    echo -n "Testing $description... "
    
    response=$(curl -s -w "%{http_code}" -o /tmp/debug_response "$APP_URL$endpoint")
    http_code=${response: -3}
    
    if [ "$http_code" -eq 200 ]; then
        echo "✅ OK ($http_code)"
        return 0
    else
        echo "❌ FAILED ($http_code)"
        return 1
    fi
}

# Function to get detailed info
get_debug_info() {
    echo "📊 Getting system information..."
    curl -s "$APP_URL/debug-info" | jq '.' 2>/dev/null || curl -s "$APP_URL/debug-info"
    echo ""
}

# Function to get logs
get_logs() {
    echo "📜 Recent application logs:"
    curl -s "$APP_URL/view-logs" | tail -20
    echo ""
}

# Function to monitor continuously
monitor_mode() {
    echo "🔄 Starting continuous monitoring (Press Ctrl+C to stop)..."
    while true; do
        clear
        echo "🔍 Laravel Debug Agent - $(date '+%H:%M:%S')"
        echo "========================================"
        
        test_endpoint "/health" "Health Check"
        test_endpoint "/" "Main Page"
        test_endpoint "/debug" "Debug Page"
        
        echo ""
        echo "📈 Response Times:"
        curl -s -w "Health: %{time_total}s\n" -o /dev/null "$APP_URL/health"
        
        sleep 10
    done
}

# Main menu
echo ""
echo "Select debugging mode:"
echo "1) Quick Health Check"
echo "2) Comprehensive System Info"
echo "3) View Recent Logs" 
echo "4) Continuous Monitoring"
echo "5) All Tests"
echo ""
read -p "Enter choice (1-5): " choice

case $choice in
    1)
        echo "🏥 Health Check Mode"
        test_endpoint "/health" "Health Check"
        test_endpoint "/health/detailed" "Detailed Health"
        ;;
    2)
        echo "📊 System Info Mode"
        get_debug_info
        ;;
    3)
        echo "📜 Logs Mode"
        get_logs
        ;;
    4)
        monitor_mode
        ;;
    5)
        echo "🔍 Running All Tests..."
        test_endpoint "/health" "Health Check"
        test_endpoint "/" "Main Page"
        test_endpoint "/debug" "Debug Info"
        test_endpoint "/health/detailed" "Detailed Health"
        echo ""
        get_debug_info
        echo ""
        get_logs
        ;;
    *)
        echo "Invalid choice. Running basic health check..."
        test_endpoint "/health" "Health Check"
        ;;
esac

echo ""
echo "🎯 Debug Agent Complete - $TIMESTAMP" 