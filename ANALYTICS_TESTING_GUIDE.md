# Analytics Tab Testing Guide

This guide explains how to test the newly implemented Analytics features in the Admin Panel.

## Features Implemented

### 1. Analytics Overview (`/admin/analytics/overview`)
- **Live Traffic Stats**:
  - Active users (last 5 minutes)
  - Events this minute
  - Events this hour
  - Unique IP addresses
- **Conversion Stats**:
  - New registrations today
  - Logins today
  - Return visits today
- **Overall Statistics**:
  - Total events
  - Events today/week/month
  - Unique users today
- **Recent Visitors Table**:
  - Shows last 10 minutes of activity
  - Displays: time, user, IP, browser, platform, country
- **Top Events** (last 7 days)
- **Auto-refresh**: Page refreshes every 30 seconds

### 2. Analytics Events (`/admin/analytics/events`)
- **Filters**:
  - Event type dropdown
  - Date range (from/to)
- **Events Table**:
  - Time, Event Type, User, IP Address, Browser, Country, Data
- **Pagination**: 50 events per page
- Shows message when no data available

### 3. Analytics Reports (`/admin/analytics/reports`)
- **Date Range Filter**: Custom date selection
- **Daily Event Count**:
  - Table view
  - Bar chart visualization (using Chart.js)
- **Events by Type**:
  - Table with percentage bars
- **Browser Distribution**
- **Geographic Distribution** (Top 10 countries)
- **Hourly Activity** (0-23 hours)

### 4. Live Traffic Tracking
Automatically tracks:
- Page visits (all pages except assets/API)
- User logins
- User registrations
- Return visits (users who return after 1+ days)
- IP addresses
- Browser and platform information
- Geographic location (placeholder - integrate with GeoIP service)
- Minute-level timing
- Conversion events

## How to Test

### Step 1: Ensure Database Tables Exist

Make sure the `analytics_events` table is created:

```bash
mysql -u root -p your_database < /path/to/mmb/install/migrations/complete_phase_updates.sql
```

### Step 2: Populate Sample Data (Optional)

To test with sample data:

```bash
mysql -u root -p your_database < /path/to/mmb/install/migrations/populate_analytics_sample_data.sql
```

This will insert:
- Sample page visits from today, yesterday, and last week
- Login/registration events
- Different browsers and platforms
- Various countries
- Conversion events

### Step 3: Access the Analytics Pages

1. **Login as Admin**:
   - Go to your site's login page
   - Login with admin credentials

2. **Navigate to Analytics**:
   - Go to `/admin/analytics/overview`
   - Check the sidebar menu for "Analytics" section

3. **Test Each Page**:

   **Overview Page** (`/admin/analytics/overview`):
   - ✓ Check if live stats are showing
   - ✓ Verify recent visitors table displays data
   - ✓ Check conversion stats
   - ✓ Verify overall statistics
   - ✓ View top events table
   - ✓ Wait 30 seconds to test auto-refresh

   **Events Page** (`/admin/analytics/events`):
   - ✓ Check if events are listed
   - ✓ Test event type filter
   - ✓ Test date range filter
   - ✓ Test pagination (if > 50 events)
   - ✓ Click "Clear" to reset filters

   **Reports Page** (`/admin/analytics/reports`):
   - ✓ Check daily event count table and chart
   - ✓ Test date range filter
   - ✓ Verify events by type with percentage bars
   - ✓ Check browser distribution
   - ✓ Check geographic distribution
   - ✓ Check hourly activity table

### Step 4: Test Live Tracking

1. **Register a new user**:
   - Go to `/register`
   - Complete registration
   - Check `/admin/analytics/events` - should show `user_register` event

2. **Login with a user**:
   - Login as any user
   - Check `/admin/analytics/events` - should show `user_login` event

3. **Browse pages**:
   - Navigate to different pages (dashboard, projects, etc.)
   - Check `/admin/analytics/overview` - should show in recent visitors
   - Check `/admin/analytics/events` - should show `page_visit` events

4. **Test return visits**:
   - Login as a user who hasn't visited recently (modify data or wait)
   - Should generate `return_visit` event

### Step 5: Test Filters and Date Ranges

**Events Page**:
- Filter by specific event type (e.g., "user_login")
- Set date range (e.g., last week)
- Combine both filters

**Reports Page**:
- Change date range to last 7 days
- Change to last 30 days
- Change to custom range

### Step 6: Verify Data Accuracy

1. **Perform known actions**:
   - Login 3 times
   - Visit 5 different pages
   - Register 1 new user

2. **Check stats**:
   - Overview should show correct counts
   - Events page should list all activities
   - Reports should reflect the changes

## Troubleshooting

### Issue: "No data showing"

**Solution**:
1. Check if `analytics_events` table exists:
   ```sql
   SHOW TABLES LIKE 'analytics_events';
   ```

2. Check if table has data:
   ```sql
   SELECT COUNT(*) FROM analytics_events;
   ```

3. If empty, run the sample data SQL or perform actual actions

### Issue: "SQL error on overview page"

**Solution**:
- Ensure MariaDB/MySQL is version 5.7+ or MariaDB 10.2+
- Check that the `DATE_SUB` function syntax is correct (we use `INTERVAL 7 DAY` not `INTERVAL 7 DAYS`)

### Issue: "Charts not displaying"

**Solution**:
- Check browser console for JavaScript errors
- Ensure Chart.js CDN is accessible: `https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js`
- Check if `$dailyStats` array has data

### Issue: "Tracking not working"

**Solution**:
1. Check if `TrafficTrackingMiddleware` is registered in Router
2. Check error logs in `/storage/logs/`
3. Verify database connection
4. Check if route is being skipped (assets, API endpoints are skipped)

## Expected Results

After successful setup and testing:

1. **Overview Page**:
   - Live stats showing active users
   - Recent visitors table populated
   - Conversion metrics accurate
   - Auto-refresh working

2. **Events Page**:
   - All events listed with details
   - Filters working correctly
   - Pagination functional
   - No SQL errors

3. **Reports Page**:
   - Chart displaying correctly
   - All stats tables showing data
   - Date filters working
   - No JavaScript errors

4. **Live Tracking**:
   - New events appear immediately
   - IP addresses captured correctly
   - Browser/platform detected
   - Timestamps accurate

## Integration with Production

For production use:

1. **GeoIP Integration**:
   - Integrate with MaxMind GeoIP2 or ip-api.com
   - Update `TrafficTracker::getCountry()` method

2. **Performance**:
   - Consider archiving old analytics data (> 90 days)
   - Add indexes for frequently queried columns
   - Use database replication for read-heavy analytics queries

3. **Privacy**:
   - Anonymize IP addresses if required by GDPR
   - Add user consent mechanism
   - Allow users to opt-out of tracking

4. **Monitoring**:
   - Set up alerts for traffic spikes
   - Monitor database performance
   - Track analytics system health

## Next Steps

1. Integrate real GeoIP service
2. Add more visualization types (pie charts, line charts)
3. Add export functionality (CSV, PDF reports)
4. Implement real-time dashboard with WebSocket
5. Add user-specific analytics
6. Create scheduled reports via email
