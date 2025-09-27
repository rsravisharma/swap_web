<?php

use App\Console\Commands\SyncUserStats;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withEvents(discover: [
        __DIR__ . '/../app/Listeners',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
     ->withSchedule(function ($schedule) {
        $schedule->command('users:sync-stats')
        ->dailyAt('02:00')              // Run at 2 AM when traffic is low
        ->environments(['production'])   // Only in production
        ->withoutOverlapping()          // Prevent overlapping runs
        ->runInBackground()             // Don't block other schedules
        ->onSuccess(function () {
            Log::info('User stats sync completed successfully');
        })
        ->onFailure(function () {
            Log::error('User stats sync failed');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
