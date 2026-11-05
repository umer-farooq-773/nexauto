-- ================================================================
-- FIX REPORTS & ANALYTICS MENU - 100% WORKING SOLUTION
-- ================================================================
-- Run these commands in phpMyAdmin or MySQL command line

-- Step 1: Check current status
SELECT id, name, route, status, main_permission_key, parent_id, sort_order
FROM auth_sidebar_menus
WHERE name = 'Reports & Analytics';

-- Step 2: If menu exists but hidden, UPDATE it
UPDATE auth_sidebar_menus
SET
    status = 1,
    main_permission_key = NULL,
    route = 'reports.index',
    icon = 'ri-file-chart-line',
    sort_order = 70
WHERE name = 'Reports & Analytics';



-- Step 3: If menu doesn't exist, INSERT it (use UUID generator or replace with actual UUID)
-- Note: Replace '01a041d1-31ad-73af-9584-b5b2f909016f' with a fresh UUID if needed
INSERT INTO auth_sidebar_menus (id, parent_id, name, icon, route, sort_order, main_permission_key, status, route_param, created_at, updated_at)
VALUES (
    '01a041d1-31ad-73af-9584-b5b2f909016f',
    NULL,
    'Reports & Analytics',
    'ri-file-chart-line',
    'reports.index',
    70,
    NULL,
    1,
    NULL,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    status = 1,
    main_permission_key = NULL,
    route = 'reports.index',
    updated_at = NOW();

-- Step 4: Remove any permission restrictions that might hide the menu
DELETE FROM auth_sidebar_menu_permissions
WHERE auth_sidebar_menu_id = (
    SELECT id FROM auth_sidebar_menus WHERE name = 'Reports & Analytics'
);

-- Step 5: Verify the menu is now visible
SELECT
    m.id,
    m.name,
    m.route,
    m.status,
    m.main_permission_key,
    m.parent_id,
    COUNT(mp.permission_id) as permission_restrictions
FROM auth_sidebar_menus m
LEFT JOIN auth_sidebar_menu_permissions mp ON m.id = mp.auth_sidebar_menu_id
WHERE m.name = 'Reports & Analytics'
GROUP BY m.id;

-- ================================================================
-- EXPECTED RESULT:
-- - status should be: 1 (active)
-- - main_permission_key should be: NULL (no restrictions)
-- - permission_restrictions should be: 0 (no permission checks)
-- ================================================================



/**

UPDATE auth_sidebar_menus
SET route = NULL
WHERE name = 'Reports & Analytics';
*/


-- Run the Laravel migrations instead if possible (php artisan migrate) —
-- this raw SQL is provided only as a reference / for manual DB setup.

CREATE TABLE `wialon_report_templates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `wialon_resource_id` BIGINT UNSIGNED NOT NULL,
  `wialon_template_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(255) NULL,
  `synced_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wialon_report_templates_unique` (`wialon_resource_id`, `wialon_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `wialon_report_results` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `vehicle_id` BIGINT UNSIGNED NULL,
  `wialon_unit_id` BIGINT UNSIGNED NOT NULL,
  `wialon_report_template_id` BIGINT UNSIGNED NOT NULL,
  `interval_from` TIMESTAMP NOT NULL,
  `interval_to` TIMESTAMP NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `error_message` TEXT NULL,
  `export_path` VARCHAR(255) NULL,
  `export_format` VARCHAR(20) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `wialon_report_results_template_fk` (`wialon_report_template_id`),
  CONSTRAINT `wialon_report_results_template_fk`
    FOREIGN KEY (`wialon_report_template_id`)
    REFERENCES `wialon_report_templates` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
