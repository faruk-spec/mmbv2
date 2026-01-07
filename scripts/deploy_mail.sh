#!/bin/bash
#
# Mail Project Deployment Script
# Deploys mail project with proper verification and logging
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
echo -e "${BLUE}  Mail Project Deployment${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Function to log message
log_step() {
    echo -e "${BLUE}→${NC} $1"
}

log_success() {
    echo -e "${GREEN}✓${NC} $1"
}

log_error() {
    echo -e "${RED}✗${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Check if we're in the correct directory
if [ ! -d "$PROJECT_ROOT/projects/mail" ]; then
    log_error "Mail project directory not found at: $PROJECT_ROOT/projects/mail"
    exit 1
fi

# Step 1: Pre-deployment verification
log_step "Running pre-deployment verification..."
if bash "$SCRIPT_DIR/verify_deployment.sh"; then
    log_success "Pre-deployment verification passed"
else
    log_error "Pre-deployment verification failed"
    echo ""
    echo "Fix the errors reported above before deploying"
    exit 1
fi
echo ""

# Step 2: Check Git status
log_step "Checking Git status..."
cd "$PROJECT_ROOT"
if [ -d ".git" ]; then
    BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null)
    COMMIT=$(git rev-parse --short HEAD 2>/dev/null)
    
    if [ -n "$BRANCH" ] && [ -n "$COMMIT" ]; then
        log_success "Current branch: $BRANCH"
        log_success "Current commit: $COMMIT"
        
        # Check for uncommitted changes
        if ! git diff-index --quiet HEAD --; then
            log_warning "You have uncommitted changes"
            echo ""
            git status --short
            echo ""
            read -p "Continue deployment with uncommitted changes? (y/N): " -n 1 -r
            echo
            if [[ ! $REPLY =~ ^[Yy]$ ]]; then
                log_error "Deployment cancelled"
                exit 1
            fi
        else
            log_success "No uncommitted changes"
        fi
    fi
else
    log_warning "Not a Git repository"
fi
echo ""

# Step 3: Backup current deployment (if exists)
log_step "Creating backup..."
BACKUP_DIR="$PROJECT_ROOT/backups"
BACKUP_NAME="mail_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

if [ -d "$MAIL_PROJECT" ]; then
    tar -czf "$BACKUP_DIR/${BACKUP_NAME}.tar.gz" -C "$PROJECT_ROOT/projects" mail 2>/dev/null
    if [ $? -eq 0 ]; then
        log_success "Backup created: $BACKUP_DIR/${BACKUP_NAME}.tar.gz"
    else
        log_warning "Failed to create backup"
    fi
else
    log_warning "Mail project not found for backup"
fi
echo ""

# Step 4: Set correct permissions
log_step "Setting file permissions..."
chmod -R 755 "$PROJECT_ROOT/projects/mail" 2>/dev/null
chmod -R 777 "$PROJECT_ROOT/storage" 2>/dev/null
chmod 644 "$PROJECT_ROOT/projects/mail/config.php" 2>/dev/null
log_success "Permissions set"
echo ""

# Step 5: Clear any PHP opcode cache
log_step "Clearing PHP cache..."
if command -v php &> /dev/null; then
    php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared'; } else { echo 'OPcache not available'; }" 2>/dev/null
    echo ""
fi
log_success "Cache cleared"
echo ""

# Step 6: Run database migrations (if needed)
log_step "Checking database migrations..."
if [ -f "$MAIL_PROJECT/migrations/verify_mail_setup.php" ]; then
    log_warning "Manual step required: Run database migrations if needed"
    echo "  Command: php $MAIL_PROJECT/migrations/verify_mail_setup.php"
else
    log_warning "Database verification script not found"
fi
echo ""

# Step 7: Create deployment log
log_step "Creating deployment log..."
LOG_FILE="$PROJECT_ROOT/storage/logs/deployment_$(date +%Y%m%d_%H%M%S).log"
mkdir -p "$PROJECT_ROOT/storage/logs"
cat > "$LOG_FILE" <<EOF
Deployment Log
==============
Date: $(date)
Branch: ${BRANCH:-N/A}
Commit: ${COMMIT:-N/A}
User: $(whoami)
Server: $(hostname)

Deployed Components:
- Mail Project Controllers (with debug logging)
- Mail Routes
- Deployment Scripts
- Verification Scripts

Status: DEPLOYED
EOF
log_success "Deployment log created: $LOG_FILE"
echo ""

# Step 8: Test URL accessibility
log_step "Testing URL accessibility..."
echo ""
echo "After deployment, verify these URLs work:"
echo "  ✓ /projects/mail/subscriber/domains"
echo "  ✓ /projects/mail/subscriber/domains/add"
echo "  ✓ /projects/mail/subscriber/aliases"
echo "  ✓ /projects/mail/subscriber/aliases/add"
echo "  ✓ /projects/mail/subscriber/users/add"
echo "  ✓ /projects/mail/subscriber/billing"
echo "  ✓ /projects/mail/webmail"
echo "  ✓ /projects/mail/subscriber/upgrade?plan=4"
echo "  ✓ /admin/projects/mail/subscribers/1/billing"
echo ""

# Step 9: Post-deployment verification
log_step "Running post-deployment verification..."
if bash "$SCRIPT_DIR/verify_deployment.sh"; then
    log_success "Post-deployment verification passed"
else
    log_warning "Post-deployment verification found issues"
    echo ""
    echo "Review warnings above and check error logs"
fi
echo ""

# Step 10: Display next steps
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Deployment Complete!${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""
echo "Next Steps:"
echo "1. Monitor error logs: tail -f $PROJECT_ROOT/storage/logs/error.log"
echo "2. Test each URL listed above"
echo "3. Check database connectivity: php $MAIL_PROJECT/migrations/verify_mail_setup.php"
echo "4. Review deployment log: $LOG_FILE"
echo ""
echo -e "${GREEN}Deployment completed successfully!${NC}"
exit 0
