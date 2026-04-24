<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InstallationTest extends TestCase
{
    #[Test]
    public function migration_creates_the_accounts_table(): void
    {
        $this->assertTrue(Schema::hasTable('accounts'));

        foreach ([
            'owner_type', 'owner_id', 'name', 'type', 'status',
            'provider', 'provider_reference', 'identifier',
            'currency', 'opening_balance_cents', 'balance_cents',
            'is_primary', 'metadata', 'deleted_at',
        ] as $column) {
            $this->assertTrue(
                Schema::hasColumn('accounts', $column),
                "accounts.{$column} missing -- consuming apps will break."
            );
        }
    }

    #[Test]
    public function config_defaults_are_loaded_from_the_package(): void
    {
        $this->assertSame('accounts', config('accounts.table'));
        $this->assertSame('api', config('accounts.route_prefix'));
        $this->assertTrue(config('accounts.compute_balance'));
    }

    #[Test]
    public function api_resource_routes_are_registered_including_refresh_balance(): void
    {
        $registered = collect(Route::getRoutes())->map(fn ($r) => $r->uri())->all();

        $this->assertContains('api/accounts', $registered);
        $this->assertContains('api/accounts/{account}', $registered);
        $this->assertContains('api/accounts/{account}/refresh-balance', $registered);
    }

    #[Test]
    public function publishable_tags_are_registered(): void
    {
        $configTag = \Illuminate\Support\ServiceProvider::$publishGroups['accounts-config'] ?? null;
        $migrationsTag = \Illuminate\Support\ServiceProvider::$publishGroups['accounts-migrations'] ?? null;

        $this->assertNotNull($configTag, 'Missing accounts-config publish tag.');
        $this->assertNotNull($migrationsTag, 'Missing accounts-migrations publish tag.');
    }

    #[Test]
    public function route_middleware_can_be_overridden_via_config(): void
    {
        $route = collect(Route::getRoutes())->first(fn ($r) => str_starts_with($r->uri(), 'api/accounts'));

        $this->assertNotNull($route);
        $this->assertContains('api', $route->gatherMiddleware());
        $this->assertNotContains('auth:sanctum', $route->gatherMiddleware());
    }
}
