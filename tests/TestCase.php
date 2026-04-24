<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        \Illuminate\Support\Facades\Schema::create('workspaces', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Whilesmart\Accounts\AccountsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Routes on; strip auth middleware for package tests.
        $app['config']->set('accounts.route_middleware', ['api']);
    }
}
