#!/bin/bash
#
# Deployment Verification Script
# Verifies that all mail project components are properly deployed and configured
#

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
MAIL_PROJECT="$PROJECT_ROOT/projects/mail"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Mail Project Deployment Verification${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Track errors
ERRORS=0
WARNINGS=0

# Function to check file exists
check_file() {
    local file="$1"
    local description="$2"
    
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $description: ${file}"
        return 0
    else
        echo -e "${RED}✗${NC} $description: ${file} ${RED}NOT FOUND${NC}"
        ((ERRORS++))
        return 1
    fi
}

# Function to check directory exists
check_directory() {
    local dir="$1"
    local description="$2"
    
    if [ -d "$dir" ]; then
        echo -e "${GREEN}✓${NC} $description: ${dir}"
        return 0
    else
        echo -e "${RED}✗${NC} $description: ${dir} ${RED}NOT FOUND${NC}"
        ((ERRORS++))
        return 1
    fi
}

# Function to check file content
check_content() {
    local file="$1"
    local pattern="$2"
    local description="$3"
    
    if grep -q "$pattern" "$file" 2>/dev/null; then
        echo -e "${GREEN}✓${NC} $description"
        return 0
    else
        echo -e "${YELLOW}⚠${NC} $description ${YELLOW}NOT FOUND${NC}"
        ((WARNINGS++))
        return 1
    fi
}

echo -e "${BLUE}1. Checking Core Files${NC}"
echo "-----------------------------------"
check_file "$PROJECT_ROOT/index.php" "Root index.php"
check_file "$PROJECT_ROOT/core/App.php" "Core App class"
check_file "$PROJECT_ROOT/core/Router.php" "Core Router class"
check_file "$PROJECT_ROOT/core/Database.php" "Database class"
check_file "$PROJECT_ROOT/config/database.php" "Database config"
echo ""

echo -e "${BLUE}2. Checking Mail Project Structure${NC}"
echo "-----------------------------------"
check_directory "$MAIL_PROJECT" "Mail project directory"
check_file "$MAIL_PROJECT/index.php" "Mail project entry point"
check_file "$MAIL_PROJECT/routes/web.php" "Mail routes file"
check_file "$MAIL_PROJECT/config.php" "Mail config file"
echo ""

echo -e "${BLUE}3. Checking Mail Controllers${NC}"
echo "-----------------------------------"
check_file "$MAIL_PROJECT/controllers/DomainController.php" "DomainController"
check_file "$MAIL_PROJECT/controllers/AliasController.php" "AliasController"
check_file "$MAIL_PROJECT/controllers/SubscriberController.php" "SubscriberController"
check_file "$MAIL_PROJECT/controllers/WebmailController.php" "WebmailController"
check_file "$MAIL_PROJECT/controllers/DashboardController.php" "DashboardController"
check_file "$MAIL_PROJECT/controllers/BaseController.php" "BaseController"
echo ""

echo -e "${BLUE}4. Checking Debug Logging in Controllers${NC}"
echo "-----------------------------------"
check_content "$MAIL_PROJECT/controllers/DomainController.php" "error_log" "DomainController has debug logging"
check_content "$MAIL_PROJECT/controllers/AliasController.php" "error_log" "AliasController has debug logging"
check_content "$MAIL_PROJECT/controllers/SubscriberController.php" "error_log" "SubscriberController has debug logging"
check_content "$MAIL_PROJECT/controllers/WebmailController.php" "error_log" "WebmailController has debug logging"
echo ""

echo -e "${BLUE}5. Checking Routes Configuration${NC}"
echo "-----------------------------------"
check_content "$MAIL_PROJECT/routes/web.php" "/subscriber/domains" "Domain routes defined"
check_content "$MAIL_PROJECT/routes/web.php" "/subscriber/aliases" "Alias routes defined"
check_content "$MAIL_PROJECT/routes/web.php" "/subscriber/users" "User routes defined"
check_content "$MAIL_PROJECT/routes/web.php" "/subscriber/billing" "Billing routes defined"
check_content "$MAIL_PROJECT/routes/web.php" "/webmail" "Webmail routes defined"
check_content "$MAIL_PROJECT/routes/web.php" "/subscriber/upgrade" "Upgrade routes defined"
echo ""

echo -e "${BLUE}6. Checking Routes are Loaded${NC}"
echo "-----------------------------------"
check_content "$MAIL_PROJECT/index.php" "routes/web.php" "Mail index.php loads routes"
echo ""

echo -e "${BLUE}7. Checking Database Tables${NC}"
echo "-----------------------------------"
if command -v mysql &> /dev/null; then
    # Try to connect to database and check tables
    DB_CONFIG="$PROJECT_ROOT/config/database.php"
    if [ -f "$DB_CONFIG" ]; then
        echo -e "${YELLOW}⚠${NC} Manual database verification required"
        echo "  Run: php $MAIL_PROJECT/migrations/verify_mail_setup.php"
        ((WARNINGS++))
    else
        echo -e "${RED}✗${NC} Database config not found"
        ((ERRORS++))
    fi
else
    echo -e "${YELLOW}⚠${NC} MySQL client not available - skipping database checks"
    ((WARNINGS++))
fi
echo ""

echo -e "${BLUE}8. Checking File Permissions${NC}"
echo "-----------------------------------"
if [ -w "$PROJECT_ROOT/storage" ]; then
    echo -e "${GREEN}✓${NC} Storage directory is writable"
else
    echo -e "${RED}✗${NC} Storage directory is not writable"
    ((ERRORS++))
fi

if [ -w "$PROJECT_ROOT/storage/logs" ]; then
    echo -e "${GREEN}✓${NC} Logs directory is writable"
else
    echo -e "${YELLOW}⚠${NC} Logs directory is not writable"
    ((WARNINGS++))
fi
echo ""

echo -e "${BLUE}9. Checking Web Server Configuration${NC}"
echo "-----------------------------------"
if [ -f "$PROJECT_ROOT/.htaccess" ]; then
    echo -e "${GREEN}✓${NC} .htaccess file exists"
    check_content "$PROJECT_ROOT/.htaccess" "RewriteEngine On" ".htaccess has rewrite rules"
else
    echo -e "${YELLOW}⚠${NC} .htaccess file not found"
    ((WARNINGS++))
fi
echo ""

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Verification Summary${NC}"
echo -e "${BLUE}========================================${NC}"
if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed!${NC}"
    echo ""
    echo -e "Deployment appears to be ${GREEN}READY${NC}"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo -e "${YELLOW}⚠ ${WARNINGS} warning(s) found${NC}"
    echo ""
    echo -e "Deployment may work but review warnings above"
    exit 0
else
    echo -e "${RED}✗ ${ERRORS} error(s) found${NC}"
    echo -e "${YELLOW}⚠ ${WARNINGS} warning(s) found${NC}"
    echo ""
    echo -e "Please fix errors before deploying"
    exit 1
fi
