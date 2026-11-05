#!/bin/bash

# Fix 500 Error on Page Reload
# This script fixes the duplicate route issue

echo "========================================"
echo "Fixing 500 Error (Duplicate Routes)"
echo "========================================"
echo ""

# Step 1: Pull latest code
echo "Step 1: Pulling latest code..."
git pull origin main
echo ""

# Step 2: Clear route cache
echo "Step 2: Clearing route cache..."
php artisan route:clear
echo ""

# Step 3: Clear all caches
echo "Step 3: Clearing all caches..."
php artisan config:clear
php artisan view:clear
php artisan cache:clear
echo ""

# Step 4: Rebuild route cache
echo "Step 4: Rebuilding route cache..."
php artisan route:cache
echo ""

# Step 5: Verify fix
echo "Step 5: Verifying fix..."
echo "Testing route cache..."
php artisan route:list --name=waybill.vehicle_route

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "✅ Fix Applied Successfully!"
    echo "========================================"
    echo ""
    echo "Next steps:"
    echo "1. Test page reload in browser"
    echo "2. Test waybill functionality"
    echo "3. Monitor logs for any issues"
else
    echo ""
    echo "========================================"
    echo "❌ Issue persists - check logs"
    echo "========================================"
fi
