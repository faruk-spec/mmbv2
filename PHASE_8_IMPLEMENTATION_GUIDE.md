# Phase 8: Performance Optimization - Implementation Guide

## Overview
This document describes the performance optimization features implemented in Phase 8 of the MMB platform.

## Important: Database Configuration

**Critical Note**: This implementation does NOT hardcode database names or credentials. All database configuration is:
- **Main Database**: Configured during installation via the setup wizard
- **Project Databases**: Configured in the admin panel for each project

Example configurations:
- Main database: `testuser` (or `mmb_main`, or any other name chosen during install)
- CodeXPro database: `codexpro` (or `mmb_codexpro`, configured in admin panel)
- ImgTxt database: `imgtxt` (or `mmb_imgtxt`, configured in admin panel)
- ProShare database: `proshare` (or `mmb_proshare`, configured in admin panel)

## Completed Features

### 1. Database Optimization

#### Added Indexes
We've created a comprehensive migration file that adds optimized indexes to all database tables.

**Location**: `/install/migrations/phase8_database_optimization.sql`

**Key Improvements**:
- Composite indexes for common query patterns (e.g., `user_id` + `created_at`)
- Indexes for date-based queries (trending, recent items)
- Indexes for filtering operations (status, visibility, etc.)
- Optimized foreign key relationships

**To Apply**:
The migration file is database-agnostic and can be applied to any database name:

```bash
# For main database (whatever name you configured during installation)
mysql -u username -p your_main_database < install/migrations/phase8_database_optimization.sql

# For project databases (apply the relevant sections to each project database)
# CodeXPro database
mysql -u codexpro_user -p codexpro_db < install/migrations/phase8_database_optimization.sql

# ImgTxt database
mysql -u imgtxt_user -p imgtxt_db < install/migrations/phase8_database_optimization.sql

# ProShare database
mysql -u proshare_user -p proshare_db < install/migrations/phase8_database_optimization.sql
```

**Note**: The migration file contains sections for each database type. Apply only the relevant sections to each database.

#### Database Maintenance
Regular maintenance tasks are documented in the migration file:
- Monthly: `ANALYZE TABLE` to update statistics
- Quarterly: `OPTIMIZE TABLE` to reclaim space
- Archive old records before cleanup

### 2. Caching System

#### Cache Class
A new file-based caching system has been implemented.

**Location**: `/core/Cache.php`

**Features**:
- Simple key-value storage with TTL support
- File-based caching (no external dependencies)
- Cache statistics and cleanup
- "Remember" pattern for lazy caching

**Usage Examples**:

```php
use Core\Cache;

// Store value in cache for 1 hour
Cache::set('user_stats_' . $userId, $stats, 3600);

// Retrieve cached value
$stats = Cache::get('user_stats_' . $userId);

// Check if cached
if (Cache::has('key')) {
    // ...
}

// Remember pattern (get or generate and cache)
$stats = Cache::remember('dashboard_stats', function() {
    return calculateDashboardStats();
}, 3600);

// Clear specific entry
Cache::delete('key');

// Clear all cache
Cache::clear();

// Get cache statistics
$stats = Cache::stats();
// Returns: total_entries, active_entries, expired_entries, total_size, total_size_mb

// Cleanup expired entries
$removed = Cache::cleanup();
```

**Cache Location**: `/storage/cache/`

**Implementation Tips**:
1. Cache expensive database queries
2. Cache computed statistics
3. Cache API responses
4. Use appropriate TTL values (shorter for frequently changing data)

### 3. Asset Optimization

#### AssetManager Class
Handles CSS/JS minification and bundling.

**Location**: `/core/AssetManager.php`

**Features**:
- CSS minification
- JavaScript minification
- Asset bundling
- Version management with manifest
- Inline asset generation

**Usage Examples**:

```php
use Core\AssetManager;

// Get versioned asset URL
$cssUrl = AssetManager::url('css/style.css');
// Output: /css/style.css?v=1234567890

// Bundle multiple CSS files
$bundledUrl = AssetManager::bundleCSS([
    'css/bootstrap.css',
    'css/custom.css',
    'css/admin.css'
], 'admin-bundle.css', true); // true = minify

// Bundle multiple JS files
$bundledUrl = AssetManager::bundleJS([
    'js/jquery.js',
    'js/bootstrap.js',
    'js/custom.js'
], 'app-bundle.js', true);

// Inline critical CSS
echo AssetManager::inlineCSS('css/critical.css');

// Inline critical JS
echo AssetManager::inlineJS('js/critical.js');

// Clear all bundles
$count = AssetManager::clearBundles();
```

**In Views**:
```php
<!-- Instead of -->
<link rel="stylesheet" href="/css/style.css">

<!-- Use -->
<link rel="stylesheet" href="<?= AssetManager::url('css/style.css') ?>">

<!-- For bundled assets -->
<link rel="stylesheet" href="<?= AssetManager::bundleCSS(['css/a.css', 'css/b.css'], 'bundle.css') ?>">
```

## Implementation Checklist

### Database Optimization
- [x] Create migration file with optimized indexes (database-agnostic)
- [x] Document maintenance procedures
- [ ] Apply migration to production databases (each with their configured names)
- [ ] Set up automated ANALYZE TABLE (monthly cron)
- [ ] Set up automated OPTIMIZE TABLE (quarterly cron)
- [ ] Implement old record archival scripts

### Caching Strategy
- [x] Implement Cache class
- [ ] Add caching to expensive queries in controllers
- [ ] Cache dashboard statistics
- [ ] Cache project statistics
- [ ] Cache user data
- [ ] Set up automated cache cleanup (daily cron)

### Code Optimization
- [x] Implement AssetManager class
- [ ] Bundle CSS files for each section
- [ ] Bundle JS files for each section
- [ ] Minify existing assets
- [ ] Implement lazy loading for images
- [ ] Add service worker for offline support

### File Storage Optimization
- [ ] Implement CDN integration (placeholder ready)
- [ ] Add image optimization on upload
- [ ] Implement chunked uploads for large files
- [ ] Background processing for file operations

## Performance Targets

### Database
- Query response time < 100ms for most queries
- No N+1 query problems
- Proper use of indexes on all filtered/joined columns

### Page Load
- First Contentful Paint (FCP) < 1.5s
- Time to Interactive (TTI) < 3.5s
- Total page size < 2MB

### API
- API response time < 200ms
- Proper caching headers
- Rate limiting in place

## Monitoring

### Database Performance
```sql
-- Check slow queries
SELECT * FROM mysql.slow_log ORDER BY query_time DESC LIMIT 20;

-- Check index usage
SHOW INDEX FROM table_name;

-- Explain query plan
EXPLAIN SELECT ...;
```

### Cache Performance
```php
// Get cache stats
$stats = Cache::stats();
echo "Total entries: " . $stats['total_entries'];
echo "Cache size: " . $stats['total_size_mb'] . " MB";
echo "Hit rate: " . ($stats['active_entries'] / $stats['total_entries'] * 100) . "%";
```

### Asset Performance
- Monitor bundle sizes
- Check asset load times
- Verify version cache busting works

## Next Steps

After implementing Phase 8:
1. Monitor performance improvements
2. Identify remaining bottlenecks
3. Consider implementing:
   - Redis for caching (if file cache becomes a bottleneck)
   - CDN for static assets
   - Database read replicas for scaling
   - Load balancer for horizontal scaling

## Maintenance

### Daily
- Run cache cleanup: `Cache::cleanup()`

### Weekly
- Review slow query log
- Monitor cache hit rates
- Check storage usage

### Monthly
- Run `ANALYZE TABLE` on all tables
- Review and optimize slow queries
- Clear old cache entries

### Quarterly
- Run `OPTIMIZE TABLE` on large tables
- Archive old records
- Review and update indexes

## Configuration

### Cache Directory
Default: `/storage/cache/`
Ensure this directory has proper permissions:
```bash
chmod 755 storage/cache
chown www-data:www-data storage/cache
```

### Asset Directory
Default: `/public/assets/`
Bundles are stored in:
- `/public/assets/css/`
- `/public/assets/js/`

### Database Configuration
Each database uses its own configuration:
- Main database: `/config/database.php` (set during installation)
- Project databases: `/projects/{project}/config.php` (configured in admin panel)

Example project config (`/projects/codexpro/config.php`):
```php
'database' => [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'codexpro',  // Can be any name chosen by admin
    'username' => 'codexpro',  // Can be any username
    'password' => 'codexpro',  // Set by admin
],
```

Optimize MySQL configuration in `my.cnf`:
```ini
[mysqld]
# Query cache (if using MySQL < 8.0)
query_cache_type = 1
query_cache_size = 64M

# InnoDB settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection settings
max_connections = 200
thread_cache_size = 16

# Slow query log
slow_query_log = 1
long_query_time = 2
```

## Troubleshooting

### Cache Issues
- If cache directory is not writable, check permissions
- Clear cache if experiencing stale data: `Cache::clear()`
- Monitor cache size and cleanup regularly

### Asset Issues
- If bundled assets are not loading, check public directory permissions
- Clear bundles and regenerate: `AssetManager::clearBundles()`
- Verify manifest.json is writable

### Database Issues
- If queries are slow after adding indexes, run `ANALYZE TABLE`
- Check for missing indexes using `EXPLAIN`
- Monitor database connection pool
- Verify database names match your configuration files

## Support

For issues or questions:
1. Check error logs in `/storage/logs/`
2. Verify all files have correct permissions
3. Review this documentation
4. Check database configuration in `/config/database.php` and project configs
5. Check the main README.md for general setup issues
