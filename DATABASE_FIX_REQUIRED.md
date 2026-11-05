# Database Migration Required - Coordinate Data Not Saving

## Problem Identified

Looking at your logs, the coordinates are showing as `0.0` when syncing to Wialon:

```
"points":[{"name":"Haripur","lat":0.0,"lng":0.0},{"name":"Islamabad","lat":0.0,"lng":0.0}]
```

But the Geoapify routing request had correct data:
```
{"name":"Haripur","lat":33.9944889,"lng":72.9331737}
{"name":"Islamabad","lat":33.6938118,"lng":73.0651511}
```

## Root Cause

The data is being **submitted from the form** but **NOT being saved to the database** because the migration for these columns hasn't been run yet.

### What Was Missing:

1. ✅ **WaybillDto** - FIXED - Now captures all coordinate fields
2. ✅ **Waybill Model** - Already has fields in `$fillable` array
3. ❓ **Database Migration** - Exists but may not have been run

## Files Fixed

### 1. app/Dto/WaybillDto.php ✅
**Added these fields:**
```php
public readonly ?string $origin_lat;
public readonly ?string $origin_lng;
public readonly ?string $destination_lat;
public readonly ?string $destination_lng;
public readonly ?string $checkpoints;
public readonly ?string $route_geometry;
public readonly ?string $wialon_route_id;
public readonly ?string $route_source;
```

## Required Action: Run Migration

The migration file `2026_08_21_000001_add_geoapify_route_columns_to_waybills_table.php` already exists and adds these columns:

- `origin_lat` - DECIMAL(10,7)
- `origin_lng` - DECIMAL(10,7)
- `destination_lat` - DECIMAL(10,7)
- `destination_lng` - DECIMAL(10,7)
- `checkpoints` - JSON
- `route_geometry` - JSON
- `wialon_route_id` - VARCHAR
- `route_source` - VARCHAR(50)

### Step 1: Check if Migration Has Been Run

```bash
# Open MySQL/phpMyAdmin and run:
DESCRIBE waybills;

# Look for these columns:
# - origin_lat
# - origin_lng
# - destination_lat
# - destination_lng
# - checkpoints
# - route_geometry
# - wialon_route_id
# - route_source
```

### Step 2: Run the Migration (If Columns Don't Exist)

```bash
# In your project terminal:
php artisan migrate

# Or run this specific migration:
php artisan migrate --path=database/migrations/2026_08_21_000001_add_geoapify_route_columns_to_waybills_table.php
```

### Step 3: Verify Columns Were Added

```bash
# In MySQL/phpMyAdmin:
DESCRIBE waybills;

# You should see the new columns listed
```

## Testing After Migration

### Test 1: Create New Waybill
1. Navigate to "Add Waybill"
2. Select origin: "Haripur"
3. Select destination: "Islamabad"
4. Add checkpoints: "Taxila", "Khanpur"
5. Click "Create Waybill"
6. Check database:
   ```sql
   SELECT 
       waybill_number,
       origin, origin_lat, origin_lng,
       destination, destination_lat, destination_lng,
       checkpoints,
       wialon_route_id
   FROM waybills 
   ORDER BY created_at DESC 
   LIMIT 1;
   ```
7. ✅ **Coordinates should NOT be 0.0 or NULL**

### Test 2: Check Wialon Sync Logs
```bash
# Check Laravel logs:
tail -f storage/logs/laravel.log

# Look for:
"points":[{"name":"Haripur","lat":33.9944889,"lng":72.9331737}...]

# NOT:
"points":[{"name":"Haripur","lat":0.0,"lng":0.0}...]
```

### Test 3: Check Wialon Dashboard
1. Login to Wialon
2. Find the route in the resource
3. Checkpoints should have proper coordinates
4. Route should be visible on map

## Database Structure

### Before Migration:
```sql
waybills
├── id
├── waybill_number
├── vehicle_id
├── driver_id
├── origin          ← Name only, no coordinates
├── destination     ← Name only, no coordinates
├── distance
├── estimated_duration
└── ... (other fields)
```

### After Migration:
```sql
waybills
├── id
├── waybill_number
├── vehicle_id
├── driver_id
├── origin
├── origin_lat      ← NEW
├── origin_lng      ← NEW
├── destination
├── destination_lat ← NEW
├── destination_lng ← NEW
├── checkpoints     ← NEW (JSON)
├── route_geometry  ← NEW (JSON)
├── wialon_route_id ← NEW
├── route_source    ← NEW
├── distance
├── estimated_duration
└── ... (other fields)
```

## What Happens After This Fix

### Before Fix:
```
User creates waybill
↓
Form submits with coordinates
↓
WaybillDto ignores coordinates (OLD)
↓
Database saves waybill without coordinates
↓
Wialon sync reads from database → Gets 0.0 for lat/lng
↓
Wialon receives invalid coordinates
```

### After Fix:
```
User creates waybill
↓
Form submits with coordinates
↓
WaybillDto captures ALL fields including coordinates (NEW)
↓
Database saves waybill WITH coordinates
↓
Wialon sync reads from database → Gets actual coordinates
↓
Wialon receives valid coordinates
↓
Route appears correctly in Wialon dashboard!
```

## Verification Checklist

After running the migration:

- [ ] Run `php artisan migrate`
- [ ] Verify columns exist: `DESCRIBE waybills;`
- [ ] Create a test waybill
- [ ] Check database for coordinate values (should NOT be 0.0)
- [ ] Check Laravel logs for proper coordinates in Wialon sync
- [ ] Check Wialon dashboard for route with correct locations
- [ ] Test page refresh - route should persist

## If Migration Fails

### Error: "Column already exists"
This means the columns were added manually. That's fine! The migration checks for existing columns with `Schema::hasColumn()`.

### Error: "Table doesn't exist"
Run all migrations: `php artisan migrate`

### Error: "SQLSTATE[42000]"
Check your database connection in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Summary

1. ✅ **DTO Fixed** - WaybillDto now captures coordinate fields
2. ✅ **Model Ready** - Waybill model has fields in $fillable
3. ⚠️ **Migration Needed** - Run `php artisan migrate` to add database columns

Once the migration runs, coordinates will save properly and Wialon sync will work!
