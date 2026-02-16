# Pending Features for QR Generator System

**Status:** Ready for New PR Implementation
**Date:** 2026-02-16
**Estimated Time:** 12-17 hours

---

## Quick Start Prompt for New PR

```
Implement pending QR Generator enhancements:

1. Settings Page (Issue #8):
   - Add Design Defaults tab (corner style, dot style, marker styles)
   - Add Logo Defaults tab (color, size, remove background)
   - Add Advanced Defaults tab (gradient, transparent bg, custom marker color)
   - Save/load all settings to database
   - Apply defaults to generate page

2. Dashboard Enhancements (Issue #9):
   - Add enhanced statistics (scans today, this week, growth %)
   - Add Recent Activity widget (last 10 QR codes)
   - Add Top Performing QRs widget (top 5 by scans)
   - Add Campaign Overview widget (optional)
   - Ensure mobile responsiveness

3. Analytics Enhancements (Issue #6):
   - Add date range filter with quick selections
   - Add CSV export functionality
   - Add Chart.js library
   - Add scan trends line chart
   - Add top QRs bar chart
   - All features responsive

Database schema updates needed for user settings.
```

---

## Issue #8: Settings Page - Missing Options (HIGH PRIORITY)

### Missing Settings to Add:

#### Design Defaults Tab (New)
- Corner Style (dropdown: square, extra-rounded, dot)
- Dot Style (dropdown: dots, rounded, square, classy, classy-rounded)
- Marker Border Style (dropdown: square, extra-rounded, dot)
- Marker Center Style (dropdown: square, extra-rounded, dot)

#### Logo Defaults Tab (New)
- Logo Color (color picker, default: #9945ff)
- Logo Size (range 0.1-0.5, default: 0.30)
- Remove Background (checkbox, default: false)

#### Advanced Defaults Tab (New)
- Gradient Enabled (checkbox, default: false)
- Gradient Color (color picker, default: #9945ff)
- Transparent Background (checkbox, default: false)
- Custom Marker Color (checkbox, default: false)
- Marker Color (color picker, default: #9945ff)

### Database Fields Needed:
```sql
ALTER TABLE user_settings ADD COLUMN default_corner_style VARCHAR(50) DEFAULT 'square';
ALTER TABLE user_settings ADD COLUMN default_dot_style VARCHAR(50) DEFAULT 'square';
ALTER TABLE user_settings ADD COLUMN default_marker_border_style VARCHAR(50) DEFAULT 'square';
ALTER TABLE user_settings ADD COLUMN default_marker_center_style VARCHAR(50) DEFAULT 'square';
ALTER TABLE user_settings ADD COLUMN default_logo_color VARCHAR(7) DEFAULT '#9945ff';
ALTER TABLE user_settings ADD COLUMN default_logo_size DECIMAL(3,2) DEFAULT 0.30;
ALTER TABLE user_settings ADD COLUMN default_logo_remove_bg TINYINT(1) DEFAULT 0;
ALTER TABLE user_settings ADD COLUMN default_gradient_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE user_settings ADD COLUMN default_gradient_color VARCHAR(7) DEFAULT '#9945ff';
ALTER TABLE user_settings ADD COLUMN default_transparent_bg TINYINT(1) DEFAULT 0;
ALTER TABLE user_settings ADD COLUMN default_custom_marker_color TINYINT(1) DEFAULT 0;
ALTER TABLE user_settings ADD COLUMN default_marker_color VARCHAR(7) DEFAULT '#9945ff';
```

---

## Issue #9: Dashboard Enhancements (MEDIUM PRIORITY)

### Widgets to Add:

#### 1. Enhanced Statistics
- Add sub-text to existing cards (Today, This Month, Growth %)
- Add 3 new stat cards: Scans Today, Scans This Week, Average Scans

#### 2. Recent Activity Widget
- Show last 10 QR codes created
- Display: Icon, Content (truncated), Relative time
- Quick actions: View, Edit links

#### 3. Top Performing QRs Widget
- Show top 5 QR codes by scan count
- Display: Content, Scan count, Progress bar
- Quick actions: View Analytics, Edit

#### 4. Campaign Overview Widget (Optional)
- Show active campaigns (limit 5)
- Display: Campaign name, QR count
- Link to view full campaign

---

## Issue #6: Analytics Enhancements (MEDIUM PRIORITY)

### Features to Add:

#### 1. Date Range Filter
- Start/End date pickers
- Quick filters: Last 7/30/90 days, All time
- Filter QR list and charts

#### 2. Export to CSV
- Export filtered analytics data
- Columns: ID, Content, Scans, Status, Created, Deleted
- Filename: `qr-analytics-YYYY-MM-DD.csv`

#### 3. Scan Trends Chart
- Line chart (Chart.js)
- X-axis: Dates (last 30 days)
- Y-axis: Scan counts
- Responsive design

#### 4. Top QRs Chart
- Horizontal bar chart
- Top 10 QR codes by scans
- Click to view details

### Dependencies:
- Chart.js v4.x: `https://cdn.jsdelivr.net/npm/chart.js`

---

## Implementation Phases

### Phase 1: Settings (4-6 hours)
1. Database migration
2. Update SettingsController
3. Update settings.php view (3 new tabs)
4. Update generate.php (load defaults)
5. Testing

### Phase 2: Dashboard (3-4 hours)
1. Update DashboardController (new queries)
2. Add widgets to dashboard.php
3. Add CSS for widgets
4. Mobile responsiveness
5. Testing

### Phase 3: Analytics (5-7 hours)
1. Add date filter UI
2. Implement filter logic
3. Add CSV export
4. Include Chart.js
5. Create chart endpoints
6. Initialize charts
7. Testing

---

## Files to Modify

### Controllers:
- `projects/qr/controllers/SettingsController.php`
- `projects/qr/controllers/DashboardController.php`
- `projects/qr/controllers/AnalyticsController.php`
- `projects/qr/controllers/QRController.php`

### Views:
- `projects/qr/views/settings.php`
- `projects/qr/views/dashboard.php`
- `projects/qr/views/analytics.php`
- `projects/qr/views/generate.php`

### Models:
- `projects/qr/models/QRModel.php`
- `projects/qr/models/UserModel.php` (if applicable)

### New Files:
- `projects/qr/assets/js/analytics.js` (chart logic)
- Database migration file

---

## Testing Checklist

### Settings:
- [ ] Save/load design defaults
- [ ] Save/load logo defaults
- [ ] Save/load advanced defaults
- [ ] Apply defaults to generate page
- [ ] Mobile responsive

### Dashboard:
- [ ] Stats accurate
- [ ] Recent activity displays
- [ ] Top QRs ranked correctly
- [ ] Campaign overview accurate
- [ ] Mobile responsive
- [ ] Empty states

### Analytics:
- [ ] Date filter works
- [ ] CSV export valid
- [ ] Scan trends chart renders
- [ ] Top QRs chart renders
- [ ] Mobile responsive
- [ ] Handle no data

---

## Success Criteria

✅ All QR generation options available as defaults in settings
✅ Dashboard provides actionable insights with widgets
✅ Analytics includes filtering, export, and visualizations
✅ All features mobile-responsive
✅ Performance optimized
✅ User-friendly interface

---

**Ready to start new PR!** 🚀
