# Cache Clearing Guide - UI/UX Changes Not Reflecting

## Problem
After pulling the latest code or deploying updates, UI/UX layout changes are not reflecting in your browser. This is caused by browser caching.

## Quick Fix - Hard Refresh

The fastest way to see the latest changes is to perform a **hard refresh** (force reload):

### Windows/Linux:
- **Chrome/Edge/Firefox:** `Ctrl + Shift + R` or `Ctrl + F5`
- **Alternative:** `Shift + F5`

### Mac:
- **Chrome/Edge:** `Cmd + Shift + R`
- **Safari:** `Cmd + Option + R`
- **Firefox:** `Cmd + Shift + R`

---

## Complete Cache Clearing (If Hard Refresh Doesn't Work)

### Google Chrome / Microsoft Edge

1. **Open Developer Tools:**
   - Press `F12` or `Ctrl + Shift + I` (Windows/Linux)
   - Press `Cmd + Option + I` (Mac)

2. **Clear Cache:**
   - Right-click the refresh button (while DevTools is open)
   - Select **"Empty Cache and Hard Reload"**

   **OR**

3. **Clear Browsing Data:**
   - Press `Ctrl + Shift + Delete` (Windows/Linux)
   - Press `Cmd + Shift + Delete` (Mac)
   - Select **"Cached images and files"**
   - Choose **"All time"** from the time range
   - Click **"Clear data"**

### Mozilla Firefox

1. **Open Menu:**
   - Click the menu button (☰) in the top-right corner
   - Select **"Settings"** or **"Options"**

2. **Clear Cache:**
   - Go to **"Privacy & Security"**
   - Scroll to **"Cookies and Site Data"**
   - Click **"Clear Data..."**
   - Check **"Cached Web Content"**
   - Click **"Clear"**

   **OR**

3. **Quick Clear:**
   - Press `Ctrl + Shift + Delete` (Windows/Linux)
   - Press `Cmd + Shift + Delete` (Mac)
   - Select **"Cache"**
   - Choose time range **"Everything"**
   - Click **"Clear Now"**

### Safari (Mac)

1. **Enable Developer Menu:**
   - Go to **Safari > Preferences**
   - Click **"Advanced"** tab
   - Check **"Show Develop menu in menu bar"**

2. **Clear Cache:**
   - Click **"Develop"** in menu bar
   - Select **"Empty Caches"**
   - Or press `Cmd + Option + E`

3. **Complete Refresh:**
   - Press `Cmd + Option + R` to reload page

---

## For Development Servers

### Disable Caching During Development

**Chrome/Edge:**
1. Open DevTools (`F12`)
2. Click the **"Network"** tab
3. Check **"Disable cache"** checkbox
4. Keep DevTools open while developing

**Firefox:**
1. Open DevTools (`F12`)
2. Click settings icon (⚙️) in top-right
3. Under **"Advanced Settings"**
4. Check **"Disable HTTP Cache (when toolbox is open)"**

---

## Server-Side Solutions (For Admins)

### What We've Implemented

The application now includes automatic cache busting:

1. **Cache-Control Headers:**
   - All QR pages send `no-cache` headers
   - Prevents browser from storing cached copies
   - Forces fresh content on every visit

2. **Version Parameters:**
   - External CSS files include version query strings
   - Example: `/css/universal-theme.css?v=20260209180742`
   - Browser treats each version as a new file

3. **Meta Tags:**
   - HTML includes cache-prevention meta tags
   - Works across different browsers

### Update UI Version

When making UI changes, update the version in `/projects/qr/views/layout.php`:

```php
// Update this timestamp when making UI changes
$uiVersion = '20260209180742'; // Format: YYYYMMDDHHMMSS
```

This forces all browsers to reload the CSS.

---

## Troubleshooting

### Still Not Seeing Changes?

1. **Try Incognito/Private Mode:**
   - Chrome/Edge: `Ctrl + Shift + N`
   - Firefox: `Ctrl + Shift + P`
   - Safari: `Cmd + Shift + N`
   - This bypasses all cache

2. **Check if Server Updated:**
   - Verify the latest code is deployed
   - Run `git pull` and restart server
   - Check file timestamps

3. **Clear DNS Cache:**
   - Windows: `ipconfig /flushdns`
   - Mac: `sudo killall -HUP mDNSResponder`
   - Linux: `sudo systemd-resolve --flush-caches`

4. **Try Different Browser:**
   - If it works in another browser, it's a cache issue
   - Clear cache in the problematic browser

5. **Check Browser Extensions:**
   - Some extensions cache aggressively
   - Try disabling ad blockers, VPNs temporarily

---

## Prevention Tips

### For Users:

1. **Always hard refresh** after updates:
   - `Ctrl + Shift + R` (Windows/Linux)
   - `Cmd + Shift + R` (Mac)

2. **Keep DevTools open** during development:
   - Enables "Disable cache" option
   - Prevents caching issues

3. **Use incognito mode** for testing:
   - No cache interference
   - Clean slate every time

### For Developers:

1. **Update version number** when changing UI:
   ```php
   $uiVersion = '20260209180742'; // Update this!
   ```

2. **Test in multiple browsers:**
   - Chrome/Edge
   - Firefox
   - Safari

3. **Document changes:**
   - Note what UI changes were made
   - Include version number in commit message

4. **Inform users:**
   - Send notification about updates
   - Include cache clearing instructions

---

## Quick Reference

| Action | Windows/Linux | Mac |
|--------|---------------|-----|
| Hard Refresh | `Ctrl + Shift + R` | `Cmd + Shift + R` |
| Clear Cache | `Ctrl + Shift + Delete` | `Cmd + Shift + Delete` |
| Developer Tools | `F12` or `Ctrl + Shift + I` | `Cmd + Option + I` |
| Incognito Mode | `Ctrl + Shift + N` | `Cmd + Shift + N` |

---

## Summary

**Immediate Fix:** Hard refresh with `Ctrl + Shift + R` (Windows/Linux) or `Cmd + Shift + R` (Mac)

**Complete Fix:** Clear browser cache through settings

**Prevention:** Application now includes automatic cache busting

**For Developers:** Update `$uiVersion` in layout.php when making UI changes

---

## Need Help?

If you're still experiencing issues after following this guide:

1. Check server logs for errors
2. Verify latest code is deployed
3. Try accessing from a different device
4. Contact system administrator

Remember: **When in doubt, hard refresh!** 🔄
