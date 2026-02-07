# Button Text Fix - Generate QR Code

## Issue
The button on the QR generation form was showing incorrect text:
- **Displayed**: "Save QR Code"  
- **Should be**: "Generate QR Code"

## Solution
Updated the button text in `projects/qr/views/generate.php` (line 163)

### Change Made
```html
<!-- Before -->
<button type="submit" class="btn btn-primary">Save QR Code</button>

<!-- After -->
<button type="submit" class="btn btn-primary">Generate QR Code</button>
```

## Why This Matters
1. **Accuracy**: The button generates AND saves the QR code, not just saves it
2. **Clarity**: "Generate" better describes the primary action
3. **Consistency**: Matches the page title "Generate QR Code"
4. **User Experience**: Users understand what the button does

## Impact
- ✅ No functional changes
- ✅ Only text label updated
- ✅ Form submission works the same
- ✅ Better UX and clarity

## Testing
After this change:
1. Visit: `/projects/qr/generate`
2. Fill out the form
3. Click "Generate QR Code" button
4. QR code generates and saves correctly

## Status
✅ **FIXED** - Button text now correctly says "Generate QR Code"

---

**Commit**: Fix button text: Change "Save QR Code" to "Generate QR Code"
**Date**: 2026-02-07
**File Changed**: `projects/qr/views/generate.php`
