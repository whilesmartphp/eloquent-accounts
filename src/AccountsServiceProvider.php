<?php

namespace Whilesmart\Accounts;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AccountsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/accounts.php', 'accounts');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/accounts.php' => config_path('accounts.php'),
        ], 'accounts-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'accounts-migrations');

        if (config('accounts.register_routes', true)) {
            Route::middleware(config('accounts.route_middleware', ['api', 'auth:sanctum']))
                ->prefix(config('accounts.route_prefix', 'api'))
                ->group(__DIR__.'/../routes/api.php');
        }
    }
}
