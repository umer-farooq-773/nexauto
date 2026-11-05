# Waybill Map Tracking Route Parameter Fix

## Problem

Error when viewing waybill details page:
```
Missing required parameter for [Route: waybill.waybill_map_tracking] 
[URI: my-account/vehicle-management/waybill/{waybill}] 
[Missing parameter: waybill]
```

## Root Cause

The route `waybill.waybill_map_tracking` expects a `{waybill}` parameter (waybill ID), but the view was passing incorrect parameters:

### Incorrect Code:
```php
// Line 26 - Passing fleet_no instead of waybill ID
route('waybill.waybill_map_tracking', $data->vehicle?->fleet_no)

// Line 107 - Same issue
route('waybill.waybill_map_tracking', $data->vehicle->fleet_no)

// Line 115 - Passing 'fleet_no' key instead of 'waybill'
route('waybill.waybill_map_tracking', ['fleet_no' => $data->vehicle->fleet_no, 'embedded' => 1])
```

### Route Definition:
```php
// routes/panel.php line 185
Route::get('{waybill}', 'vehicleTracking')->name('waybill_map_tracking');
```

The route expects:
- **Parameter name:** `waybill`
- **Parameter value:** Waybill ID (UUID)
- **What was passed:** Vehicle fleet number (string like "VEH-001")

## Solution

Changed all three occurrences to pass the correct waybill ID:

### Fixed Code:

**Line 26 (Top button):**
```php
<a href="{{ route('waybill.waybill_map_tracking', $data->id) }}"
    target="_blank"
    class="btn btn-sm btn-outline-primary shadow-sm">
    <i class="fas fa-map-marked-alt me-1"></i>
    {{ __('View Map') }}
</a>
```

**Line 107 (Open full map button):**
```php
<a href="{{ route('waybill.waybill_map_tracking', $data->id) }}"
   target="_blank"
   class="btn btn-sm btn-outline-primary">
    <i class="fas fa-expand me-1"></i> {{ __('Open full map') }}
</a>
```

**Line 115 (Iframe src):**
```php
<iframe
    src="{{ route('waybill.waybill_map_tracking', ['waybill' => $data->id, 'embedded' => 1]) }}"
    title="{{ __('Live vehicle location map') }}"
    class="waybill-live-map__frame"
    ...>
</iframe>
```

## Changes Summary

| Location | Before | After |
|----------|--------|-------|
| Line 26 | `$data->vehicle?->fleet_no` | `$data->id` |
| Line 107 | `$data->vehicle->fleet_no` | `$data->id` |
| Line 115 | `['fleet_no' => $data->vehicle->fleet_no, ...]` | `['waybill' => $data->id, 'embedded' => 1]` |

## Why This Matters

### Before Fix:
```
URL Generated: /waybill/VEH-001?embedded=1
Expected URL:  /waybill/{uuid}?embedded=1
Result: ❌ Route not found / Parameter mismatch
```

### After Fix:
```
URL Generated: /waybill/9b5c7f8a-4d3e-4b2c-8e6f-1a2b3c4d5e6f?embedded=1
Expected URL:  /waybill/{uuid}?embedded=1
Result: ✅ Route matches correctly
```

## Controller Expectation

The `WaybillController::vehicleTracking()` method expects a **Waybill model** to be injected via route model binding:

```php
public function vehicleTracking(Waybill $waybill)
{
    // Laravel automatically finds the waybill by ID
    // If we pass fleet_no, it fails because it's not a valid UUID
    return view('waybill.map-tracking', compact('waybill'));
}
```

## Testing

After this fix:

1. ✅ Navigate to any waybill details page
2. ✅ Click "View Map" button (top right)
3. ✅ Click "Open full map" button (map section)
4. ✅ Iframe map loads correctly
5. ✅ No route parameter errors in logs

## Files Modified

- `resources/views/auth/pages/waybill/show.blade.php`
  - Line 26: Fixed top "View Map" button
  - Line 107: Fixed "Open full map" button
  - Line 115: Fixed iframe src parameter

## Error Prevention

To prevent this error in the future:

### ✅ Correct Pattern:
```php
// When the route parameter is {waybill}, pass waybill ID
route('waybill.waybill_map_tracking', $waybill->id)

// With query parameters
route('waybill.waybill_map_tracking', ['waybill' => $waybill->id, 'embedded' => 1])
```

### ❌ Incorrect Pattern:
```php
// Don't pass vehicle fleet number
route('waybill.waybill_map_tracking', $waybill->vehicle->fleet_no)

// Don't use wrong parameter name
route('waybill.waybill_map_tracking', ['fleet_no' => ...])
```

## Route Reference

```php
// Route definition (routes/panel.php)
Route::get('{waybill}', 'vehicleTracking')->name('waybill_map_tracking');

// Generates URLs like:
// /my-account/vehicle-management/waybill/{waybill-uuid}
// /my-account/vehicle-management/waybill/9b5c7f8a-4d3e-4b2c-8e6f-1a2b3c4d5e6f
```

---

**Fix Date:** 2026-08-24  
**Error Type:** Missing Route Parameter  
**Severity:** Critical (page crash)  
**Status:** ✅ Fixed
