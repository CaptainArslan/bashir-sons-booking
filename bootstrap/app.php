<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            '2fa.pending' => \App\Http\Middleware\TwoFactorPending::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // Apply user status check to all web routes
        // This middleware checks if authenticated users are banned on every request
        // Using fresh() ensures we get the latest status from database even if user is already logged in
        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('app:expire-holds')->everyMinute();
        $schedule->command('bookings:expire')->everyMinute();

        // Create trips for today automatically at 1:00 AM daily
        $schedule->command('trips:create-for-day')
            ->dailyAt('01:00')
            ->timezone('Asia/Karachi')
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
