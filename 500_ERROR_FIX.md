# 500 Error on Page Reload - FIXED ✅

## Issue Description
Users were experiencing **500 Internal Server Error** when reloading any page (signin, home, etc.) after deploying the waybill update fix.

## Error Message
```
LogicException: Unable to prepare route [ajax/waybill/vehicle/{vehicleId}/route] for serialization. 
Another route has already been assigned name [waybill.vehicle_route].
```

## Root Cause

**Duplicate route names** were defined in two different route files:

### 1. In `routes/web.php` (line 183):
```php
Route::get('/ajax/waybill/vehicle/{vehicleId}/route', [WaybillController::class, 'getVehicleRoute'])
    ->name('waybill.vehicle_route');
```

### 2. In `routes/panel.php` (line 194):
```php
// Inside the waybill route group
Route::get('vehicle-route/{vehicleId}', 'getVehicleRoute')
    ->name('vehicle_route');  
// This becomes 'waybill.vehicle_route' due to group prefix
```

When Laravel tries to cache routes, it detects the duplicate name and throws an exception.

## Why It Happened

The routes in `routes/web.php` were likely added for AJAX calls outside the authenticated area, but duplicate routes already existed in `routes/panel.php` within the waybill resource group. When the route cache was rebuilt (after deployment or on page reload in production), Laravel detected the conflict.

## The Fix

**Removed the duplicate routes from `routes/web.php`:**

### Before:
```php
// AJAX: Get in-transit waybills for a vehicle
Route::get('/ajax/vehicle/{vehicle}/waybills-in-transit', ...);

// AJAX: Get Wialon route for a vehicle
Route::get('/ajax/waybill/vehicle/{vehicleId}/route', ...)->name('waybill.vehicle_route');

// AJAX: Get drivers for a vehicle  
Route::get('/ajax/waybill/vehicle/{vehicleId}/drivers', ...)->name('waybill.vehicle_drivers');
```

### After:
```php
// Note: Waybill AJAX routes are now defined in routes/panel.php within the waybill group
// Removed duplicate routes to prevent "route name already assigned" error on page reload
```

## Files Modified

1. **routes/web.php** - Removed duplicate waybill AJAX routes

## Verification

After the fix, route caching works correctly:

```bash
php artisan route:clear
php artisan route:cache
# ✅ Routes cached successfully
```

## Testing Steps

1. Clear route cache:
   ```bash
   php artisan route:clear
   ```

2. Cache routes:
   ```bash
   php artisan route:cache
   ```

3. Reload any page (signin, dashboard, etc.)
   - ✅ Should load without 500 error

4. Verify waybill routes still work:
   ```bash
   php artisan route:list --path=waybill
   ```

## Deployment to Live Server

```bash
# 1. Pull the latest code
git pull origin main

# 2. Clear and recache routes
php artisan route:clear
php artisan route:cache

# 3. Clear other caches
php artisan config:clear
php artisan view:clear
php artisan cache:clear

# 4. Test page reload
# Visit signin page or any page and refresh
```

## Why This Error Only Showed on Reload

In development environments (APP_ENV=local, APP_DEBUG=true):
- Routes are loaded dynamically on each request
- No route caching is used by default
- Duplicate route errors might be ignored or handled differently

In production environments (APP_ENV=production):
- Routes are cached for performance
- Laravel validates all routes during caching
- Duplicate route names cause fatal errors
- Page reloads trigger route cache validation

## Prevention

To prevent similar issues in the future:

1. **Always check for existing routes** before adding new ones:
   ```bash
   php artisan route:list --path=desired-path
   ```

2. **Test route caching locally** before deploying:
   ```bash
   php artisan route:cache
   # If this fails, fix before deploying
   ```

3. **Use consistent naming conventions** for routes to avoid conflicts

4. **Document route organization** - keep related routes in the same file

## Related Routes

All waybill-related routes are now properly defined in `routes/panel.php`:

```php
Route::controller(WaybillController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('create', 'create')->name('create');
    Route::post('create', 'store')->name('store');
    Route::get('edit/{waybill}', 'edit')->name('edit');
    Route::put('update/{waybill}', 'update')->name('update');
    Route::delete('delete/{waybill}', 'destroy')->name('destroy');
    
    // AJAX routes
    Route::get('vehicle-drivers/{vehicleId}', 'getVehicleDrivers')->name('vehicle_drivers');
    Route::get('vehicle-route/{vehicleId}', 'getVehicleRoute')->name('vehicle_route');
    Route::get('in-transit-waybills/{vehicleId}', 'getInTransitWaybills')->name('in_transit_waybills');
    
    // Additional routes...
});
```

## Summary

✅ **Issue:** Duplicate route names causing 500 error on page reload  
✅ **Root Cause:** Same route name defined in both `web.php` and `panel.php`  
✅ **Fix:** Removed duplicates from `web.php`  
✅ **Status:** Fixed and tested  
✅ **Impact:** All pages now load correctly after reload

---

**Fixed Date:** September 18, 2026  
**Issue Type:** Route Configuration Error  
**Severity:** Critical (Production Breaking)  
**Resolution Time:** Immediate
