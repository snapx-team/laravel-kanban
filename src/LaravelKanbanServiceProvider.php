<?php

namespace Xguard\LaravelKanban;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Xguard\LaravelKanban\Commands\CreateAdmin;
use Xguard\LaravelKanban\Commands\DeleteKanbanEmployeesWithDeletedUsers;
use Xguard\LaravelKanban\Commands\MoveErpDataInTaskToErpShareables;
use Xguard\LaravelKanban\Commands\NotifyOfTasksWithDeadlineInNext24;
use Xguard\LaravelKanban\Commands\ReplaceAllReporterIdsFromUserToEmployee;
use Xguard\LaravelKanban\Commands\SetLoggableTypeAndLoggableIdOnExistingLogs;
use Xguard\LaravelKanban\Http\Middleware\CheckHasAccess;
use Illuminate\Support\Facades\Storage;
use Xguard\LaravelKanban\AWSStorage\S3Storage;

class LaravelKanbanServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Xguard\LaravelKanban\Http\Controllers\LaravelKanbanController');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'Xguard\LaravelKanban');
        $this->mergeConfigFrom(__DIR__.'/../config.php', 'laravel_kanban');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        app('router')->aliasMiddleware('laravel_kanban_role_check', CheckHasAccess::class);
        $this->loadMigrationsFrom(__DIR__ . '/Http/Middleware');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/seeds');
        $this->loadFactoriesFrom(__DIR__ . '/database/factories');


        $this->commands([
            CreateAdmin::class, MoveErpDataInTaskToErpShareables::class,
            ReplaceAllReporterIdsFromUserToEmployee::class, DeleteKanbanEmployeesWithDeletedUsers::class,
            SetLoggableTypeAndLoggableIdOnExistingLogs::class, NotifyOfTasksWithDeadlineInNext24::class
        ]);


        include __DIR__ . '/routes/web.php';

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/laravel-kanban'),
        ], 'laravel-kanban-assets');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command(DeleteKanbanEmployeesWithDeletedUsers::class)->daily();
            $schedule->command(NotifyOfTasksWithDeadlineInNext24::class)->hourly();
            
        });

        $this->app->singleton(S3Storage::class, function () {
            $disk = env('APP_ENV') === 'production' ? 'kanyeban-s3' : 'local-s3';
            return Storage::disk($disk);
        });
    }
}
