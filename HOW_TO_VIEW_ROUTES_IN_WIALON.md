# How to View Routes in Wialon Dashboard

After creating a waybill route in your local portal, here's how to view it in Wialon:

## Step 1: Access Wialon Dashboard
1. Log in to your Wialon account at https://hosting.wialon.com
2. Navigate to the **Monitoring** tab

## Step 2: Find Your Vehicle
1. In the left sidebar, find your vehicle by:
   - Fleet number (unit name)
   - Or search by vehicle registration number
2. Click on the vehicle/unit

## Step 3: View Route Information
Route data is stored in the unit's custom fields. To view:

### Method 1: Unit Properties
1. Right-click on the unit in the list
2. Select **"Properties"** or **"Edit"**
3. Go to the **"Custom Fields"** or **"Admin Fields"** tab
4. Look for these fields:
   - **waybill_route_summary** - Shows: "Origin -> Checkpoint1 -> Checkpoint2 -> Destination"
   - **waybill_number** - The waybill number (e.g., "0002")
   - **waybill_origin** - Starting location
   - **waybill_destination** - End location
   - **waybill_route_data** - Complete route with coordinates (JSON format)

### Method 2: Unit Info Panel
1. Select the unit in the monitoring view
2. Look at the info panel on the right side
3. Scroll down to **"Additional Information"** or **"Custom Fields"**
4. The route fields will be displayed there

## What the Data Looks Like

**Example waybill_route_summary:**
```
Rehmanabad Station -> AFIP Laboratory CMH Rawalpindi -> Saddar -> Islamabad Fruit and Vegetable Market -> Railway Carriage Factory Road
```

**Example waybill_route_data (JSON):**
```json
{
  "waybill_number": "0002",
  "route_name": "Route: 0002 (Rehmanabad Station -> Railway Carriage Factory Road)",
  "points": [
    {"n": "Rehmanabad Station", "y": 33.6364508, "x": 73.0749822, "r": 100},
    {"n": "AFIP Laboratory CMH Rawalpindi", "y": 33.5798719, "x": 73.0459138, "r": 100},
    ...
  ]
}
```

## Important Notes

- **Custom fields may take a few moments** to appear after creating a route
- If you don't see the fields, refresh the Wialon interface
- The coordinates (y = latitude, x = longitude) can be used for:
  - Creating geofences
  - Route analysis
  - Reporting and tracking

## Troubleshooting

**If route data doesn't appear:**
1. Check the Laravel logs at `storage/logs/laravel.log`
2. Look for "Wialon route sync completed successfully"
3. Verify the vehicle has a valid `unit_id` in your database
4. Ensure the Wialon unit exists and is accessible with your API token

**If you need to visualize routes on the map:**
- Use the coordinates from `waybill_route_data` to create geofences
- Or use Wialon's reporting features to show the route path
