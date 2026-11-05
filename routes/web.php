<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inaam\Installer\Middleware\ApplicationStatus;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

// Route::middleware(ApplicationStatus::class)->group(function () {
// Route::get('/', function () {
//     return view('welcome');
// })->name('welcome');
// });

Route::get('/php82', function () {
    return "<pre>" . shell_exec('/opt/plesk/php/8.2/bin/php -v') . "</pre>";
});

Route::get('/install-dompdf', function () {

    $path = '/var/www/vhosts/champsconsults.com/vma.champsconsults.com';

    $composer = shell_exec(
        "cd {$path} && /opt/plesk/php/8.2/bin/php /usr/lib/plesk-9.0/composer.phar require barryvdh/laravel-dompdf 2>&1"
    );

    $artisan = shell_exec(
        "cd {$path} && /opt/plesk/php/8.2/bin/php artisan vendor:publish --provider=\"Barryvdh\\DomPDF\\ServiceProvider\" 2>&1"
    );

    return "<pre>{$composer}\n\n{$artisan}</pre>";
});

Route::get('/permissions', function () {
    return "<pre>" . shell_exec(
        'cd /var/www/vhosts/champsconsults.com/vma.champsconsults.com && ls -l composer.json composer.lock'
    ) . "</pre>";
});

Route::get('/owner', function () {
    return "<pre>" . shell_exec('ls -ld /var/www/vhosts/champsconsults.com/vma.champsconsults.com') . "</pre>";
});


Route::get('/clear', [ToolController::class, 'clearCache']);
Route::get('/migrate', [ToolController::class, 'migrateFresh']);
Route::get('/update-credentials', function() {
    try {
        // Get parameters from URL
        $oldEmail = request()->query('old_email');
        $newEmail = request()->query('new_email');
        $oldDirectorEmail = request()->query('old_director_email');
        $newDirectorEmail = request()->query('new_director_email');
        $password = request()->query('password', 'admin'); // Default password is 'admin'

        // Validate required parameters
        if (!$oldEmail || !$newEmail || !$oldDirectorEmail || !$newDirectorEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required parameters. Please provide: old_email, new_email, old_director_email, new_director_email',
                'example' => '/update-credentials?old_email=admin@gmail.com&new_email=umerf1417@gmail.com&old_director_email=client.director@gmail.com&new_director_email=coachselector@gmail.com&password=admin'
            ], 400);
        }

        // Update admin user
        $admin = DB::table('users')->where('email', $oldEmail)->update([
            'email' => $newEmail,
            'password' => Hash::make($password),
            'updated_at' => now()
        ]);

        // Update client director user
        $director = DB::table('users')->where('email', $oldDirectorEmail)->update([
            'email' => $newDirectorEmail,
            'password' => Hash::make($password),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Credentials updated successfully!',
            'details' => [
                'admin_updated' => $admin > 0,
                'director_updated' => $director > 0,
                'admin_old_email' => $oldEmail,
                'admin_new_email' => $newEmail,
                'director_old_email' => $oldDirectorEmail,
                'director_new_email' => $newDirectorEmail,
                'password' => $password . ' (hashed)'
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error updating credentials: ' . $e->getMessage()
        ], 500);
    }
});
Auth::routes(
    [
        'verify' => true,
        'login' => false,
        'register' => false,
        'logout' => false
    ]
);

    Route::get('/debug-log', function () {
        return nl2br(file_get_contents(storage_path('logs/laravel.log')));
    });

    Route::get('/clear-log', function () {

        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        return 'Laravel log cleared successfully.';
    });



    Route::get('/run-driver-mate-pivot-migration', function () {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_06_000002_create_driver_mate_pivot_table.php',
            '--force' => true, // Required on production
        ]);

        return nl2br(Artisan::output());
    });

Route::group(
    [
        // 'prefix' => LaravelLocalization::setLocale(),
        // 'middleware' => [
        //     'localeSessionRedirect',
        //     'localizationRedirect',
        //     'localeViewPath',
        // ],
    ],
    function () {
        Route::controller(FrontendController::class)->group(function () {
            Route::get('/', 'welcome')->name('welcome');
            Route::get('post/{slug}', 'welcome')->name('post.detail');
        });

        Route::prefix('/account/')->middleware('CheckRegister')->group(function () {
            Route::controller(LoginController::class)->group(function () {
                Route::get('signin', 'showLoginForm')->name('login');
                Route::post('signin', 'login');
            });

            Route::controller(RegisterController::class)->group(function () {
                Route::get('signup', 'showRegistrationForm')->name('register');
                Route::post('signup', 'register');
            });

            Route::get('verify', function () {
                return view('auth.verify');
            })->name('verify_email')->middleware('CheckVerifiedAccount');

            Route::get('auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])->name('social.redirect');
            Route::get('auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])->name('social.callback');
        });
    }
);

// Note: Waybill AJAX routes are now defined in routes/panel.php within the waybill group
// Removed duplicate routes to prevent "route name already assigned" error on page reload

// AJAX: Geoapify location search & routing
Route::get('/ajax/geoapify/autocomplete', [\App\Http\Controllers\GeoapifyController::class, 'autocomplete'])->name('geoapify.autocomplete');
Route::post('/ajax/geoapify/routing', [\App\Http\Controllers\GeoapifyController::class, 'routing'])->name('geoapify.routing');

