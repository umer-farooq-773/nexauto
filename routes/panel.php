<?php

use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthSidebarMenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverDetailController;
use App\Http\Controllers\DriverMateController;
use App\Http\Controllers\RouteTripController;
use App\Http\Controllers\WaybillController;
use App\Http\Controllers\VehicleMaintenanceController;
use App\Http\Controllers\VehicleMaintenanceItemController;
use App\Http\Controllers\ImpoundmentController;
use App\Http\Controllers\VehicleBreakdownController;
use App\Http\Controllers\VehicleBoggedDownController;
use App\Http\Controllers\IllegalDetentionController;
use App\Http\Controllers\AccidentController;
use App\Http\Controllers\DriverMisconductController;
use App\Http\Controllers\BreathaliserTestController;
use App\Http\Controllers\ExportCsvController;
use App\Http\Controllers\GoodsInspectionController;
use App\Http\Controllers\IllegalHaulageController;
use App\Http\Controllers\IllegalShortageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RouteDiversionController;
use App\Http\Controllers\WaybillFileController;
use App\Http\Controllers\IncidentChatController;
use App\Http\Controllers\ChatNotificationController;
use App\Http\Controllers\DriverReportController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\WialonReportGenerator;
use App\Http\Controllers\IncidentMapController;
use App\Http\Controllers\IncidentTrackingController;
use App\Http\Controllers\VehicleHistoryController;
use App\Http\Controllers\WialonSettingsSyncController;
use Illuminate\Support\Facades\Route;

Route::post('/logout', [HomeController::class, 'logout'])->name('logout');

Route::group(
    ['middleware' => 'checkMail'],
    function () {
        Route::get('/', [HomeController::class, 'index'])->name('auth');
        Route::get('/dashboard/chart-data', [HomeController::class, 'chartFilterData'])->name('dashboard.chart_data');

        Route::post('uploadPostImage', [UserController::class, 'uploadPostImage'])->name('uploadPostImage');

        Route::get('profile', [UserController::class, 'editprofile'])->name('myprofile');
        Route::put('edit-my-profile', [UserController::class, 'updatemyprofile'])->name('updatemyprofile');
        Route::post('wialon/sync-vehicle-settings', [WialonSettingsSyncController::class, 'push'])
            ->middleware('can:edit_vehicle')
            ->name('wialon.sync_vehicle_settings');

        Route::prefix('export')->name('export.')->group(function () {
            Route::post('/columns', [ExportCsvController::class, 'getColumns'])->name('csv.columns');
            Route::post('/csv', [ExportCsvController::class, 'export'])->name('csv.export');
        });

        Route::prefix('map')->name('map.')->group(function(){
            Route::get('/',[MapController::class,'index'])->name('view');
        });

        Route::get('privacy-safety', [UserController::class, 'safety_privacy'])->name('safety_privacy');
        Route::get('notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
        Route::get('notifications', [NotificationController::class, 'all'])->name('notifications.all');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::post('notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');

        // Chat message unread count (used by navbar badge)
        Route::get('chat/unread-count', [ChatNotificationController::class, 'unreadCount'])->name('chat.unread_count');

        // Incident chat notification endpoints
        Route::get('incident-chat/unread-count', [ChatNotificationController::class, 'incidentChatUnreadCount'])->name('incident_chat.unread_count');
        Route::post('incident-chat/{incidentType}/{incidentId}/mark-read', [ChatNotificationController::class, 'markIncidentChatAsRead'])->name('incident_chat.mark_read');

        Route::get('password-change', [ResetPasswordController::class, 'changePassword'])->name('change_password');
        Route::post('change-password/update', [ResetPasswordController::class, 'updatePassword'])->name('update_password');

        Route::prefix('users')->as('users.')->controller(UserController::class)->middleware(['isAdmin'])->group(function () {
            Route::get('create', 'create')->name('create')->middleware('can:add_user');
            Route::get('show/{user}', 'show')->name('show')->middleware('can:view_user');
            Route::post('store', 'store')->name('store')->middleware('can:add_user');
            Route::get('edit/{user}', 'edit')->name('edit')->middleware('can:edit_user');
            Route::put('update/{user}', 'update')->name('update')->middleware('can:edit_user');
            Route::delete('delete/{user}', 'destroy')->name('destroy')->middleware('can:delete_user');
            Route::get('{user}/list', 'getStaff')->name('index')->middleware(['can:all_user', 'validateRole']);
        });

        Route::prefix('sidebar-menu')->as('menues.')->controller(AuthSidebarMenuController::class)->group(function () {
            Route::get('', 'index')->name('index')->middleware(['can:all_sidebar_menu']);
            Route::post('store', 'store')->name('store')->middleware('can:add_sidebar_menu');
            Route::get('featureDetail/{id}', 'showAjax')->name('featureDetail')->middleware('can:view_sidebar_menu');
        });

        Route::prefix('roles')->as('roles.')->controller(RoleController::class)->group(function () {
            Route::get('create', 'create')->name('create')->middleware('can:add_role');
            Route::get('', 'index')->name('index')->middleware('can:all_role');
            Route::post('store', 'store')->name('store')->middleware('can:add_role');
            Route::put('update/{role}', 'update')->name('update')->middleware('can:edit_role');
            Route::delete('delete/{role}', 'destroy')->name('destroy')->middleware('can:delete_role');
            Route::get('detail/{role}', 'show')->name('show')->middleware('can:view_role');
            Route::get('edit/{role}', 'edit')->name('edit')->middleware('can:edit_role');

            Route::post('assign/permission', 'assign_permission')->name('assign_permission')->middleware('can:assign_permission');
        });

        Route::prefix('incident-report')->as('incident_report.')->controller(\App\Http\Controllers\DriverReportController::class)->group(function () {
            Route::get('', 'index')->name('index');
            Route::get('data', 'data')->name('data');       // AJAX JSON for live filtering
            Route::get('export', 'export')->name('export');  // Excel download
        });

        Route::prefix('s')->as('settings.')->middleware('can:site_setting')->controller(SettingController::class)->group(function () {

            Route::get('{blade}', 'index')->name('index');

            Route::put('basic_info/update', 'basic_info')->name('basic_info');
            Route::put('smtp/update', 'smtp_update')->name('smtp_update');
            Route::post('smtp/test', 'smtp_test')->name('smtp_test');
            Route::put('social-logins/update', 'social_logins_update')->name('social_logins_update');
            Route::put('registration/update', 'registeration_update')->name('registeration_update');
            Route::post('clear-cache', 'clear_cache')->name('clear_cache');

            Route::post('update/default-language', 'update_default_language')->name('update_default_language');
            Route::post('install/language', 'install_language')->name('install_language');
            Route::post('active/language', 'active_language')->name('active_language');
        });

        Route::prefix('blog')->as('blog.')->middleware('can:manage_blog')->group(function () {
            Route::prefix('category')->as('category.')->controller(CategoryController::class)->middleware('can:manage_category')->group(function () {
                Route::get('{id?}', 'index')->name('index');
                Route::post('store', 'store')->name('store');
                Route::put('update/{category}', 'update')->name('update');
                Route::delete('delete/{category}', 'destroy')->name('destroy');
            });

            Route::resource('post', PostController::class)->middleware('can:manage_post');
        });

        Route::prefix('vehicle-management')->group(function () {
            Route::as('vehicle.')->controller(VehicleController::class)->group(function () {
                Route::get('', 'vehicle_home')->name('index')->middleware('can:vehicle_management');
                Route::get('chart-data', 'chartFilterData')->name('chart_data')->middleware('can:vehicle_management');
                Route::get('vehicle-detail', 'index')->name('list')->middleware('can:vehicle_management');
                Route::get('create', 'create')->name('create')->middleware('can:add_vehicle');
                Route::get('show/{vehicle}', 'show')->name('show')->middleware('can:view_vehicle');
                Route::post('store', 'store')->name('store')->middleware('can:add_vehicle');
                Route::get('edit/{vehicle}', 'edit')->name('edit')->middleware('can:edit_vehicle');
                Route::put('update/{vehicle}', 'update')->name('update')->middleware('can:edit_vehicle');
                Route::delete('delete/{vehicle}', 'destroy')->name('destroy')->middleware('can:delete_vehicle');
                Route::get('{vehicle}/map-tracking', 'vehicleTracking')->name('vehicle_map_tracking');
            });

            Route::prefix('driver')->controller(DriverDetailController::class)->as('driver.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:driver_management');
                Route::get('create', 'create')->name('create')->middleware('can:add_driver');
                Route::get('show/{driver}', 'show')->name('show')->middleware('can:view_driver');
                Route::post('store', 'store')->name('store')->middleware('can:add_driver');
                Route::get('edit/{driver}', 'edit')->name('edit')->middleware('can:edit_driver');
                Route::put('update/{driver}', 'update')->name('update')->middleware('can:edit_driver');
                Route::get('resend-credentials/{driver}', 'resendCredentials')->name('resend_credentials')->middleware('can:edit_driver');
                Route::delete('delete/{driver}', 'destroy')->name('destroy')->middleware('can:delete_driver');
            });

            Route::prefix('driver-mate')->controller(DriverMateController::class)->as('driver_mate.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:driver_mate_management');
                Route::get('create', 'create')->name('create')->middleware('can:add_driver_mate');
                Route::get('show/{driver_mate}', 'show')->name('show')->middleware('can:view_driver_mate');
                Route::post('store', 'store')->name('store')->middleware('can:add_driver_mate');
                Route::get('edit/{driver_mate}', 'edit')->name('edit')->middleware('can:edit_driver_mate');
                Route::put('update/{driver_mate}', 'update')->name('update')->middleware('can:edit_driver_mate');
                Route::delete('delete/{driver_mate}', 'destroy')->name('destroy')->middleware('can:delete_driver_mate');
            });

            Route::prefix('route-trip')->controller(RouteTripController::class)->as('route_trip.')->group(function () {
                Route::get('', 'index')->name('index');
                Route::get('show/{route_trip}', 'show')->name('show');
                Route::post('store', 'store')->name('store');
                Route::get('edit/{route_trip}', 'edit')->name('edit');
                Route::put('update/{route_trip}', 'update')->name('update');
                Route::delete('delete/{route_trip}', 'destroy')->name('destroy');
            });

            Route::prefix('waybill')->controller(WaybillController::class)->as('waybill.')->group(function () {
                Route::get('', 'index')->name('index');
                Route::get('awaiting', 'awaiting')->name('awaiting');
                Route::get('show/{waybill}', 'showWithIncRec')->name('show');
                Route::post('store', 'store')->name('store');
                Route::get('edit/{waybill}', 'edit')->name('edit');
                Route::put('update/{waybill}', 'update')->name('update');
                Route::put('{waybill}/update-status', 'updateStatus')->name('update_status');
                Route::get('{waybill}/incident-status', 'checkIncidentStatus')->name('incident_status');
                Route::delete('delete/{waybill}', 'destroy')->name('destroy');
                Route::get('vehicle-drivers/{vehicleId}', 'getVehicleDrivers')->name('vehicle_drivers');
                Route::get('vehicle-route/{vehicleId}', 'getVehicleRoute')->name('vehicle_route');
                Route::get('in-transit-waybills/{vehicleId}', 'getInTransitWaybills')->name('in_transit_waybills');
                Route::get('{waybill}/print/full', 'printFull')->name('print_full');
                Route::get('{waybill}/print/detail', 'printDetail')->name('print_detail');
                Route::get('{waybill}', 'vehicleTracking')->name('waybill_map_tracking');

                Route::prefix('file')->as('file.')->controller(WaybillFileController::class)->group(function () {
                    Route::get('id/{waybill}', 'waybillFiles')->name('index');
                    Route::post('id/{waybill}/store', 'store')->name('store');
                    Route::post('id/{waybill}/replace/{file}', 'replace')->name('replace');
                    Route::delete('id/{waybill}/delete/{file}', 'deleteFile')->name('destroy');
                });
            });

            Route::prefix('maintenance')->controller(VehicleMaintenanceController::class)->as('maintenance.')->group(function () {
                Route::get('', 'index')->name('index');
                Route::get('history', 'history')->name('history');
                Route::get('history/{item}', 'historyShow')->name('history.show');
                Route::get('show/{maintenance}', 'show')->name('show');
                Route::post('store', 'store')->name('store');
                Route::get('edit/{maintenance}', 'edit')->name('edit');
                Route::put('update/{maintenance}', 'update')->name('update');
                Route::delete('delete/{maintenance}', 'destroy')->name('destroy');
                Route::post('approve/{maintenance}', 'clientApprove')->name('client_approve');
                Route::patch('status/{maintenance}', 'statusUpdate')->name('status_update');
                Route::post('payment/{maintenance}', 'paymentProcess')->name('payment_process');

                // Maintenance Dynamic Work & Component Items
                Route::post('{maintenance}/items', [VehicleMaintenanceItemController::class, 'store'])->name('items.store');
                Route::put('{maintenance}/items/{item}', [VehicleMaintenanceItemController::class, 'update'])->name('items.update');
                Route::delete('{maintenance}/items/{item}', [VehicleMaintenanceItemController::class, 'destroy'])->name('items.destroy');

                Route::prefix('files')->as('file.')->group(function () {
                    Route::get('{maintenance}', 'maintenanceFiles')->name('index');
                    Route::post('{maintenance}/upload', 'storeMaintenanceFiles')->name('store');
                    Route::post('{maintenance}/replace/{file}', 'replace')->name('replace');
                    Route::delete('{maintenance}/delete/{file}', 'deleteFile')->name('destroy');
                });
            });
        });

        Route::get('reports-analytics', [WialonReportGenerator::class, 'index'])
            ->name('reports.index');

        Route::post('reports-analytics/sync', [WialonReportGenerator::class, 'sync'])
            ->name('reports.sync');

        Route::post('reports-analytics/templates/sync', [WialonReportGenerator::class, 'syncTemplates'])
            ->name('reports.templates.sync');

        Route::post('reports-analytics/generate', [WialonReportGenerator::class, 'generate'])
            ->name('reports.generate');

        Route::post('reports-analytics/export', [WialonReportGenerator::class, 'export'])
            ->name('reports.export');

        Route::get('incidents/track', [IncidentTrackingController::class, 'index'])->name('incident.track');
        Route::get('incidents/track/data', [IncidentTrackingController::class, 'getData'])->name('incident.track.data');
        Route::get('incidents/all-map', [IncidentMapController::class, 'index'])->name('incident.all_map');
        Route::get('incident-map', [IncidentMapController::class, 'index'])->name('incidents.all_map');
        Route::get('incidents/map/{type}/{id}', [IncidentMapController::class, 'singleMap'])->name('incident.single_map');
        Route::get('incident-map/{type}/{id}', [IncidentMapController::class, 'singleMap'])->name('incidents.single_map');

        Route::get('vehicle-history', [VehicleHistoryController::class, 'index'])->name('vehicle_history.index');
        Route::get('vehicle-history/track-data', [VehicleHistoryController::class, 'getTrackData'])->name('vehicle_history.track_data');
        Route::get('vehicle-history/export', [VehicleHistoryController::class, 'exportReport'])->name('vehicle_history.export');

        Route::prefix('incident-intervention')->group(function () {
            Route::get('all-map', [IncidentMapController::class, 'index'])->name('incident_intervention.all_map');
            Route::prefix('impoundment')->controller(ImpoundmentController::class)->as('impoundment.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:incident_intervention');
                Route::get('show/{impoundment}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:incident_intervention');
                Route::get('edit/{impoundment}', 'edit')->name('edit')->middleware('can:incident_intervention');
                Route::put('update/{impoundment}', 'update')->name('update')->middleware('can:incident_intervention');
                Route::delete('delete/{impoundment}', 'destroy')->name('destroy')->middleware('can:incident_intervention');
                Route::post('approve/{impoundment}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{impoundment}', 'statusUpdate')
                    ->name('status_update')
                    ->middleware('can:incident_intervention');
                Route::post('payment/{impoundment}', 'paymentProcess')->name('payment_process')->middleware('can:incident_intervention');
                Route::post('status/{impoundment}', 'changeStatus')
                ->name('change_status')
                ->middleware('can:incident_intervention');
            });

            Route::prefix('vehicle-breakdown')->controller(VehicleBreakdownController::class)->as('vehicle_breakdown.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:incident_intervention');
                Route::get('show/{vehicle_breakdown}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:incident_intervention');
                Route::get('edit/{vehicle_breakdown}', 'edit')->name('edit')->middleware('can:incident_intervention');
                Route::put('update/{vehicle_breakdown}', 'update')->name('update')->middleware('can:incident_intervention');
                Route::delete('delete/{vehicle_breakdown}', 'destroy')->name('destroy')->middleware('can:incident_intervention');
                Route::post('approve/{vehicle_breakdown}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{vehicle_breakdown}', 'statusUpdate')->name('status_update')->middleware('can:incident_intervention');
                Route::post('payment/{vehicle_breakdown}', 'paymentProcess')->name('payment_process')->middleware('can:incident_intervention');
            });

            Route::prefix('vehicle-bogged-down')->controller(VehicleBoggedDownController::class)->as('vehicle_bogged_down.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:bogged_down');
                Route::get('show/{vehicle_bogged_down}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:add_bogged_down');
                Route::get('edit/{vehicle_bogged_down}', 'edit')->name('edit')->middleware('can:edit_bogged_down');
                Route::put('update/{vehicle_bogged_down}', 'update')->name('update')->middleware('can:edit_bogged_down');
                Route::delete('delete/{vehicle_bogged_down}', 'destroy')->name('destroy')->middleware('can:delete_bogged_down');
                Route::post('approve/{vehicle_bogged_down}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{vehicle_bogged_down}', 'statusUpdate')->name('status_update')->middleware('can:bogged_down');
                Route::post('payment/{vehicle_bogged_down}', 'paymentProcess')->name('payment_process')->middleware('can:bogged_down');
            });

            Route::prefix('illegal-detention')->controller(IllegalDetentionController::class)->as('illegal_detention.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:incident_intervention');
                Route::get('show/{illegal_detention}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:incident_intervention');
                Route::get('edit/{illegal_detention}', 'edit')->name('edit')->middleware('can:incident_intervention');
                Route::put('update/{illegal_detention}', 'update')->name('update')->middleware('can:incident_intervention');
                Route::delete('delete/{illegal_detention}', 'destroy')->name('destroy')->middleware('can:incident_intervention');
                Route::post('approve/{illegal_detention}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{illegal_detention}', 'statusUpdate')->name('status_update')->middleware('can:incident_intervention');
                Route::post('payment/{illegal_detention}', 'paymentProcess')->name('payment_process')->middleware('can:incident_intervention');
            });

            Route::prefix('illegal-haulage')->controller(IllegalHaulageController::class)->as('illegal_haulage.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:illegal_haulage');
                Route::get('show/{illegal_haulage}', 'show')->name('show')->middleware('can:view_illegal_haulage');
                Route::post('store', 'store')->name('store')->middleware('can:add_illegal_haulage');
                Route::get('edit/{illegal_haulage}', 'edit')->name('edit')->middleware('can:edit_illegal_haulage');
                Route::put('update/{illegal_haulage}', 'update')->name('update')->middleware('can:edit_illegal_haulage');
                Route::delete('delete/{illegal_haulage}', 'destroy')->name('destroy')->middleware('can:delete_illegal_haulage');
                Route::post('approve/{illegal_haulage}', 'clientApprove')->name('client_approve')->middleware('can:illegal_haulage');
                Route::patch('status/{illegal_haulage}', 'statusUpdate')->name('status_update')->middleware('can:illegal_haulage');
            });

            Route::prefix('illegal-shortage')->controller(IllegalShortageController::class)->as('illegal_shortage.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:illegal_shortage');
                Route::get('show/{illegal_shortage}', 'show')->name('show')->middleware('can:view_illegal_shortage');
                Route::post('store', 'store')->name('store')->middleware('can:add_illegal_shortage');
                Route::get('edit/{illegal_shortage}', 'edit')->name('edit')->middleware('can:edit_illegal_shortage');
                Route::put('update/{illegal_shortage}', 'update')->name('update')->middleware('can:edit_illegal_shortage');
                Route::delete('delete/{illegal_shortage}', 'destroy')->name('destroy')->middleware('can:delete_illegal_shortage');
                Route::post('approve/{illegal_shortage}', 'clientApprove')->name('client_approve')->middleware('can:illegal_shortage');
                Route::patch('status/{illegal_shortage}', 'statusUpdate')->name('status_update')->middleware('can:illegal_shortage');
            });

            Route::prefix('breathaliser-test')->controller(BreathaliserTestController::class)->as('breathaliser_test.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:breathaliser_test');
                Route::get('show/{breathaliser_test}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:add_breathaliser_test');
                Route::get('edit/{breathaliser_test}', 'edit')->name('edit')->middleware('can:edit_breathaliser_test');
                Route::put('update/{breathaliser_test}', 'update')->name('update')->middleware('can:edit_breathaliser_test');
                Route::delete('delete/{breathaliser_test}', 'destroy')->name('destroy')->middleware('can:delete_breathaliser_test');
                Route::post('approve/{breathaliser_test}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{breathaliser_test}', 'statusUpdate')->name('status_update')->middleware('can:breathaliser_test');
            });

            Route::prefix('accident')->controller(AccidentController::class)->as('accident.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:incident_intervention');
                Route::get('show/{accident}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:incident_intervention');
                Route::get('edit/{accident}', 'edit')->name('edit')->middleware('can:incident_intervention');
                Route::put('update/{accident}', 'update')->name('update')->middleware('can:incident_intervention');
                Route::delete('delete/{accident}', 'destroy')->name('destroy')->middleware('can:incident_intervention');
                Route::post('approve/{accident}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{accident}', 'statusUpdate')->name('status_update')->middleware('can:incident_intervention');
                Route::post('payment/{accident}', 'paymentProcess')->name('payment_process')->middleware('can:incident_intervention');
            });

            Route::prefix('route-diversion')->controller(RouteDiversionController::class)->as('route_diversion.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:route_diversion');
                Route::get('show/{route_diversion}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:add_route_diversion');
                Route::get('edit/{route_diversion}', 'edit')->name('edit')->middleware('can:edit_route_diversion');
                Route::put('update/{route_diversion}', 'update')->name('update')->middleware('can:edit_route_diversion');
                Route::delete('delete/{route_diversion}', 'destroy')->name('destroy')->middleware('can:delete_route_diversion');
                Route::post('approve/{route_diversion}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{route_diversion}', 'statusUpdate')->name('status_update')->middleware('can:route_diversion');
            });

            Route::prefix('driver-misconduct')->controller(DriverMisconductController::class)->as('driver_misconduct.')->group(function () {
                Route::get('', 'index')->name('index')->middleware('can:driver_misconduct');
                Route::get('show/{driver_misconduct}', 'show')->name('show')->middleware('can:incident_intervention');
                Route::post('store', 'store')->name('store')->middleware('can:add_driver_misconduct');
                Route::get('edit/{driver_misconduct}', 'edit')->name('edit')->middleware('can:edit_driver_misconduct');
                Route::put('update/{driver_misconduct}', 'update')->name('update')->middleware('can:edit_driver_misconduct');
                Route::delete('delete/{driver_misconduct}', 'destroy')->name('destroy')->middleware('can:delete_driver_misconduct');
                Route::post('approve/{driver_misconduct}', 'clientApprove')->name('client_approve')->middleware('can:incident_intervention');
                Route::patch('status/{driver_misconduct}', 'statusUpdate')->name('status_update')->middleware('can:driver_misconduct');
            });
        });

        // Incident Chat — shared across all incident types
        Route::prefix('incident-chat')->controller(IncidentChatController::class)->as('incident_chat.')->group(function () {
            Route::get('{incidentType}/{incidentId}', 'fetch')->name('fetch');
            Route::post('{incidentType}/{incidentId}', 'send')->name('send');
        });

        Route::prefix('goods-inspection')->group(function () {
            Route::prefix('inspection')->controller(GoodsInspectionController::class)->as('goods_inspection.')->group(function () {
                Route::middleware('can:goods_inspection')->group(function () {
                    Route::get('', 'index')->name('index');
                    Route::get('show/{goods_inspection}', 'show')->name('show');
                    Route::post('store', 'store')->name('store');
                    Route::post('evaluate-incidents', 'evaluateIncidents')->name('evaluate_incidents');
                    Route::post('create-incident', 'createLinkedIncident')->name('create_incident');
                    Route::get('findings/{finding}/edit', 'editFinding')->name('finding.edit');
                    Route::put('findings/{finding}', 'updateFinding')->name('finding.update');
                    Route::get('edit/{goods_inspection}', 'edit')->name('edit');
                    Route::put('update/{goods_inspection}', 'update')->name('update');
                    Route::post('approve/{goods_inspection}', 'clientApprove')->name('client_approve');
                    Route::delete('delete/{goods_inspection}', 'destroy')->name('destroy');
                });
            });
        });

        // These routes are only for frontend testing. when we work on backend we have to remove these routes.
        // do not use these routes in the production or development.

        Route::get('check_waybill_detail', function () {
            return view('test_dummy.check_waybill_detail');
        })->name('check_waybill_detail');

        Route::get('check_service_history', function () {
            return view('test_dummy.check_service_history');
        })->name('check_service_history');

        Route::get('acident', function () {
            return view('test_dummy.acident');
        })->name('acident');

        Route::get('illegal_detention', function () {
            return view('test_dummy.illegal_detention');
        })->name('illegal_detention');

        Route::get('impoundment', function () {
            return view('test_dummy.impoundment');
        })->name('impoundment');

        Route::get('inccident', function () {
            return view('test_dummy.inccident');
        })->name('inccident');

        Route::get('vehicle_breakdown', function () {
            return view('test_dummy.vehicle_breakdown');
        })->name('vehicle_breakdown');

        Route::get('vehicle_bogged_down', function () {
            return view('test_dummy.vehicle_bogged_down');
        })->name('vehicle_bogged_down');

        Route::get('incident_home', function () {
            return view('test_dummy.incident_home');
        })->name('incident_home');
    }

);
