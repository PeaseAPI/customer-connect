<?php

use App\Http\Middleware\CheckModuleEnabled;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\SetCompanyContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        channels: __DIR__.'/../routes/channels.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API 中间件组
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // 别名注册
        $middleware->alias([
            'company' => SetCompanyContext::class,
            'subscription' => CheckSubscription::class,
            'module' => CheckModuleEnabled::class,
            'super_admin' => EnsureSuperAdmin::class,
            'api_key' => \App\Http\Middleware\AuthenticateApiKey::class,
        ]);

        // 排除不需要多租户检查的路由
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
