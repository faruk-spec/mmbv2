#!/bin/bash
# Mail Project Database Migration Script
# This script applies all necessary migrations to the database

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Get database credentials
echo "Mail Project Database Migration Script"
echo "======================================="
echo ""

read -p "Database host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "Database name: " DB_NAME
if [ -z "$DB_NAME" ]; then
    print_error "Database name is required"
    exit 1
fi

read -p "Database user: " DB_USER
if [ -z "$DB_USER" ]; then
    print_error "Database user is required"
    exit 1
fi

read -sp "Database password: " DB_PASS
echo ""

if [ -z "$DB_PASS" ]; then
    print_error "Database password is required"
    exit 1
fi

# Test database connection
print_info "Testing database connection..."
if ! mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT 1" &> /dev/null; then
    print_error "Failed to connect to database"
    exit 1
fi
print_info "Database connection successful"

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Define migration files in order
MIGRATIONS=(
    "fix_table_names.sql"
    "create_billing_history_table.sql"
    "update_domains_table.sql"
    "update_mailboxes_table.sql"
    "update_aliases_table.sql"
    "add_currency_column.sql"
)

echo ""
print_info "Will run ${#MIGRATIONS[@]} migration files"
echo ""

# Run each migration
for migration in "${MIGRATIONS[@]}"; do
    migration_file="$SCRIPT_DIR/$migration"
    
    if [ ! -f "$migration_file" ]; then
        print_warning "Migration file not found: $migration (skipping)"
        continue
    fi
    
    print_info "Running migration: $migration"
    
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$migration_file"; then
        print_info "✓ Migration completed: $migration"
    else
        print_error "✗ Migration failed: $migration"
        read -p "Continue with remaining migrations? (y/n): " CONTINUE
        if [ "$CONTINUE" != "y" ]; then
            print_error "Migration process aborted"
            exit 1
        fi
    fi
    echo ""
done

echo ""
print_info "All migrations completed!"
echo ""

# Verify migrations
print_info "Verifying database structure..."

# Check for double-prefix tables
DOUBLE_PREFIX=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SHOW TABLES LIKE 'mail_mail_%'")
if [ ! -z "$DOUBLE_PREFIX" ]; then
    print_warning "Found tables with double prefix:"
    echo "$DOUBLE_PREFIX"
    echo ""
else
    print_info "✓ No double-prefix tables found"
fi

# Check if billing_history table exists
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESC mail_billing_history" &> /dev/null; then
    print_info "✓ mail_billing_history table exists"
else
    print_warning "✗ mail_billing_history table not found"
fi

# Check if aliases has subscriber_id
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESC mail_aliases" 2>/dev/null | grep -q "subscriber_id"; then
    print_info "✓ mail_aliases has subscriber_id column"
else
    print_warning "✗ mail_aliases missing subscriber_id column"
fi

# Check if domains has dkim columns
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESC mail_domains" 2>/dev/null | grep -q "dkim_private_key"; then
    print_info "✓ mail_domains has DKIM columns"
else
    print_warning "✗ mail_domains missing DKIM columns"
fi

# Check if mailboxes has signature column
if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DESC mail_mailboxes" 2>/dev/null | grep -q "signature"; then
    print_info "✓ mail_mailboxes has signature column"
else
    print_warning "✗ mail_mailboxes missing signature column"
fi

echo ""
print_info "Migration verification complete"
print_info "Check the output above for any warnings"
