<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\HostWorkspace;
use Tests\TestCase;
use Whilesmart\Accounts\Models\Account;

class AccountApiTest extends TestCase
{
    #[Test]
    public function post_creates_an_account(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $response = $this->postJson('/api/accounts', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'name' => 'UBA Operating',
            'type' => 'bank',
            'provider' => 'UBA',
            'currency' => 'USD',
            'opening_balance_cents' => 100_000,
            'is_primary' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'UBA Operating');
        $this->assertSame(1, Account::count());
    }

    #[Test]
    public function post_rejects_without_required_name_and_currency(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $response = $this->postJson('/api/accounts', [
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
            'type' => 'bank',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'currency']);
    }

    #[Test]
    public function index_filters_by_owner(): void
    {
        $a = HostWorkspace::create(['name' => 'A']);
        $b = HostWorkspace::create(['name' => 'B']);

        $a->accounts()->create(['name' => 'a1', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD']);
        $a->accounts()->create(['name' => 'a2', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD']);
        $b->accounts()->create(['name' => 'b1', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD']);

        $response = $this->getJson('/api/accounts?owner_type='.urlencode(HostWorkspace::class).'&owner_id='.$a->id);

        $response->assertStatus(200);
        $response->assertJsonPath('data.meta.total', 2);
    }

    #[Test]
    public function refresh_balance_route_persists_computed_value(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active',
            'currency' => 'USD', 'opening_balance_cents' => 42_000, 'balance_cents' => 0,
        ]);

        $response = $this->postJson("/api/accounts/{$account->id}/refresh-balance");

        $response->assertStatus(200);
        $this->assertSame(42_000, $account->fresh()->balance_cents);
    }

    #[Test]
    public function delete_soft_deletes_the_account(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD',
        ]);

        $response = $this->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $this->assertSame(0, Account::count());
        $this->assertSame(1, Account::withTrashed()->count());
    }
}
