# Task: Add PDF Download and Status Change buttons to Impoundment show page

## Pending Steps:
- [ ] 2. Run `php artisan view:clear && php artisan cache:clear`
- [ ] 3. Test buttons on http://127.0.0.1:8000/my-account/incident-intervention/impoundment/show/019df1b8-26ab-709f-94e8-8248cb09c16b (PDF prints details, status updates via POST).

## Completed Steps:
- [x] 1. Edit `resources/views/auth/pages/incident/impoundment/show.blade.php` to add buttons row before main content div, Bootstrap modal for status change, JS for print PDF and modal toggle, and print CSS.




