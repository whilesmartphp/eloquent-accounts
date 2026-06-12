<?php

namespace Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\HostWorkspace;
use Tests\TestCase;
use Whilesmart\OwnerAccess\Contracts\OwnerAuthorizer;

class AccountAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(OwnerAuthorizer::class, new class implements OwnerAuthorizer
        {
            public function authorize(?Authenticatable $user, string $ownerType, mixed $ownerId): bool
            {
                return false;
            }

            public function scope(Builder $query, ?Authenticatable $user, string $ownerTypeColumn = 'owner_type', string $ownerIdColumn = 'owner_id'): Builder
            {
                return $query->whereRaw('0 = 1');
            }
        });
    }

    #[Test]
    public function store_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $this->postJson('/api/accounts', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'name' => 'Denied Account',
            'currency' => 'USD',
        ])->assertForbidden();
    }

    #[Test]
    public function show_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD',
        ]);

        $this->getJson("/api/accounts/{$account->id}")->assertForbidden();
    }

    #[Test]
    public function update_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD',
        ]);

        $this->putJson("/api/accounts/{$account->id}", [
            'name' => 'Renamed',
        ])->assertForbidden();

        $this->assertSame('A', $account->fresh()->name);
    }

    #[Test]
    public function destroy_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD',
        ]);

        $this->deleteJson("/api/accounts/{$account->id}")->assertForbidden();

        $this->assertNotNull($account->fresh());
    }

    #[Test]
    public function refresh_balance_returns_403_when_authorizer_denies(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active',
            'currency' => 'USD', 'opening_balance_cents' => 9000, 'balance_cents' => 0,
        ]);

        $this->postJson("/api/accounts/{$account->id}/refresh-balance")->assertForbidden();

        $this->assertSame(0, $account->fresh()->balance_cents);
    }

    #[Test]
    public function index_applies_scope_from_authorizer(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD',
        ]);

        $response = $this->getJson('/api/accounts')->assertOk();

        $this->assertSame(0, $response->json('data.meta.total'),
            'Denying scope should filter out all rows.');
    }
}
