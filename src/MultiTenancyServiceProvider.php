<?php

namespace Calema\MultiTenancy;

use Calema\MultiTenancy\Middleware\IdentifyTenant;
use Calema\MultiTenancy\Services\TenantManager;
use Illuminate\Support\ServiceProvider;

class MultiTenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Config
        $this->mergeConfigFrom(
            __DIR__.'/../config/multi-tenancy.php',
            'multi-tenancy'
        );

        // TenantManager をシングルトンとして登録
        $this->app->singleton(TenantManager::class, function ($app) {
            return new TenantManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'multi-tenancy');

        // Publishable resources
        if ($this->app->runningInConsole()) {
            // Config
            $this->publishes([
                __DIR__.'/../config/multi-tenancy.php' => config_path('multi-tenancy.php'),
            ], 'multi-tenancy-config');

            // Migrations
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'multi-tenancy-migrations');

            // Views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/multi-tenancy'),
            ], 'multi-tenancy-views');
        }

        // Middleware
        $this->app['router']->aliasMiddleware('tenant', IdentifyTenant::class);
    }
}
