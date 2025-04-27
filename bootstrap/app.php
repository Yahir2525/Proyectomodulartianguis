<?php
use App\Http\Middleware\AdminRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'is_admin'=> AdminRole::class,
            'permission' => \Spatie\Permission\Middlewares\Permission::class,
            // 'auth' => \App\Http\Middleware\Authenticate::class, 
            // 'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
            // 'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
            // 'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // También puedes agregar middleware global (por ejemplo, sesiones, cookies, CSRF, etc.)
        // $middleware->group('web', [
        //     // \Illuminate\Cookie\Middleware\EncryptCookies::class,
        //     // \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        //     // \Illuminate\Session\Middleware\StartSession::class,
        //     // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        //     // \App\Http\Middleware\VerifyCsrfToken::class,
        //     // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
