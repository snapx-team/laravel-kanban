<?php

namespace Xguard\LaravelKanban;

use Illuminate\Support\ServiceProvider;
use Xguard\LaravelKanban\Commands\CreateAdmin;
use Xguard\LaravelKanban\Http\Middleware\CheckHasAccess;

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
        $this->commands([CreateAdmin::class]);

        include __DIR__ . '/routes/web.php';

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/laravel-kanban'),
        ], 'laravel-kanban-assets');
    }
}
