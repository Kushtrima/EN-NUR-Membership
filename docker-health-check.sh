#!/bin/bash

# EN NUR Membership - Docker Health Check Script
# This script provides comprehensive health monitoring for the Docker container

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[HEALTH]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[OK]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Health check results
HEALTH_STATUS="OK"
CHECKS_PASSED=0
CHECKS_TOTAL=0

# Function to run health check
run_check() {
    local check_name="$1"
    local check_command="$2"
    
    CHECKS_TOTAL=$((CHECKS_TOTAL + 1))
    print_status "Checking $check_name..."
    
    if eval "$check_command" > /dev/null 2>&1; then
        print_success "$check_name: PASSED"
        CHECKS_PASSED=$((CHECKS_PASSED + 1))
        return 0
    else
        print_error "$check_name: FAILED"
        HEALTH_STATUS="ERROR"
        return 1
    fi
}

# Start health checks
print_status "🏥 Starting comprehensive health check..."
echo ""

# 1. Basic web server check
run_check "Apache Web Server" "curl -f http://localhost:80/health"

# 2. PHP functionality check
run_check "PHP Processing" "php -r 'echo \"PHP OK\";'"

# 3. Laravel application check
run_check "Laravel Application" "php artisan --version"

# 4. Database connectivity check
run_check "Database Connection" "php artisan tinker --execute='DB::connection()->getPdo();'"

# 5. File permissions check
run_check "Storage Permissions" "test -w storage/logs"

# 6. Cache functionality check
run_check "Configuration Cache" "test -f bootstrap/cache/config.php"

# 7. Required PHP extensions check
run_check "PHP Extensions" "php -m | grep -q pdo_pgsql && php -m | grep -q mbstring"

# 8. Memory usage check
run_check "Memory Usage" "test $(free -m | awk 'NR==2{printf \"%.0f\", $3*100/$2}') -lt 90"

# 9. Disk space check
run_check "Disk Space" "test $(df / | tail -1 | awk '{print $5}' | sed 's/%//') -lt 90"

# 10. Application routes check
run_check "Application Routes" "curl -f http://localhost:80/test-route"

echo ""
print_status "📊 Health Check Summary:"
echo "  - Checks Passed: $CHECKS_PASSED/$CHECKS_TOTAL"
echo "  - Overall Status: $HEALTH_STATUS"

if [ "$HEALTH_STATUS" = "OK" ]; then
    print_success "🎉 All systems operational!"
    exit 0
else
    print_error "❌ Health check failed!"
    exit 1
fi 