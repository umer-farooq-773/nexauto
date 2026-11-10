<?php

use App\Constants\Constants;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('my-account')
                ->group(base_path('routes/panel.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo(Constants::DASHBOARD_URL);
        $middleware->web(append: [
            \App\Http\Middleware\RestrictDriverToPortal::class,
        ]);
        $middleware->alias([
            'CheckVerifiedAccount' => \App\Http\Middleware\CheckVerifiedAccount::class,
            'isAdmin' => \App\Http\Middleware\IsAdmin::class,
            'checkMail' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'CheckRegister' => \App\Http\Middleware\CheckRegisterStatus::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            'localize'                => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
            'localizationRedirect'    => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect'   => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localeCookieRedirect'    => \Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
            'localeViewPath'          => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
            'validateRole' => \App\Http\Middleware\ValidateUserRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, $request) {
            if (!$request->expectsJson()) {
                return redirect()->back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors($e->errors());
            }
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if (!$request->expectsJson()) {
                return redirect()->route('login')
                    ->with('error', 'Your session has expired. Please sign in again.');
            }
        });

        $exceptions->render(function (TokenMismatchException $e, $request) {
            if (!$request->expectsJson()) {
                return redirect()->route('login')
                    ->with('error', 'Your session has expired. Please sign in again.');
            }
        });

        $exceptions->render(function (UnauthorizedException $e, $request) {
            if (!$request->expectsJson()) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to perform this action.');
            }
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if (!$request->expectsJson()) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to perform this action.');
            }
        });

        $exceptions->render(function (HttpException $e, $request) {
            if (!$request->expectsJson()) {
                if ($e->getStatusCode() === 403) {
                    return redirect()->back()
                        ->with('error', 'You do not have permission to access this page.');
                }

                if ($e->getStatusCode() === 404) {
                    return response()->view('errors.404', [], 404);
                }

                if ($e->getStatusCode() === 503) {
                    return response()->view('errors.503', [], 503);
                }
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if (!$request->expectsJson()) {
                return response()->view('errors.404', [], 404);
            }
        });

        $exceptions->report(function (Throwable $e) {
            \Illuminate\Support\Facades\Log::error($e->getMessage(), [
                'exception' => $e,
            ]);
        });

        $exceptions->render(function (Throwable $e, $request) {
            \Illuminate\Support\Facades\Log::error('Unhandled exception occurred: ' . $e->getMessage(), [
                'exception' => $e,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            if (!$request->expectsJson() && !app()->isLocal() && !config('app.debug')) {
                return response()->view('errors.500', [], 500);
            }
        });
    })
    ->withProviders([
        \App\Providers\EventServiceProvider::class,
    ])
    ->create();
