<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\Support\HostWorkspace;
use Tests\TestCase;
use Whilesmart\Accounts\Enums\AccountStatus;
use Whilesmart\Accounts\Enums\AccountType;
use Whilesmart\Accounts\Models\Account;

class AccountModelTest extends TestCase
{
    #[Test]
    public function a_workspace_can_own_accounts(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $account = $ws->accounts()->create([
            'name' => 'UBA Main',
            'type' => AccountType::Bank->value,
            'status' => AccountStatus::Active->value,
            'provider' => 'UBA',
            'currency' => 'USD',
            'opening_balance_cents' => 100_000,
            'is_primary' => true,
        ]);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertSame(1, $ws->accounts()->count());
        $this->assertSame(AccountType::Bank, $account->fresh()->type);
        $this->assertSame(AccountStatus::Active, $account->fresh()->status);
    }

    #[Test]
    public function primary_account_helper_narrows_by_currency(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $ws->accounts()->create([
            'name' => 'USD main', 'type' => 'bank', 'status' => 'active',
            'currency' => 'USD', 'is_primary' => true,
        ]);

        $ws->accounts()->create([
            'name' => 'EUR main', 'type' => 'bank', 'status' => 'active',
            'currency' => 'EUR', 'is_primary' => true,
        ]);

        $this->assertSame('USD main', $ws->primaryAccount('USD')->name);
        $this->assertSame('EUR main', $ws->primaryAccount('EUR')->name);
        $this->assertNull($ws->primaryAccount('GBP'));
    }

    #[Test]
    public function primary_account_without_currency_returns_any_primary(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $ws->accounts()->create([
            'name' => 'Non-primary', 'type' => 'bank', 'status' => 'active',
            'currency' => 'USD', 'is_primary' => false,
        ]);
        $primary = $ws->accounts()->create([
            'name' => 'Primary XAF', 'type' => 'mobile_money', 'status' => 'active',
            'currency' => 'XAF', 'is_primary' => true,
        ]);

        $this->assertSame($primary->id, $ws->primaryAccount()->id);
    }

    #[Test]
    public function balance_equals_opening_balance_when_no_payments_or_expenses_exist(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $account = $ws->accounts()->create([
            'name' => 'Cash register', 'type' => 'cash', 'status' => 'active',
            'currency' => 'USD', 'opening_balance_cents' => 250_000,
        ]);

        $this->assertSame(250_000, $account->computeBalanceCents());
    }

    #[Test]
    public function refresh_balance_writes_computed_value_to_the_stored_column(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);

        $account = $ws->accounts()->create([
            'name' => 'Cash', 'type' => 'cash', 'status' => 'active',
            'currency' => 'USD', 'opening_balance_cents' => 77_000, 'balance_cents' => 0,
        ]);

        // Column starts at 0; refresh writes the computed balance.
        $this->assertSame(0, $account->balance_cents);
        $account->refreshBalance();

        $this->assertSame(77_000, $account->fresh()->balance_cents);
    }

    #[Test]
    public function is_primary_casts_to_boolean(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active',
            'currency' => 'USD', 'is_primary' => 1,
        ]);

        $this->assertTrue($account->fresh()->is_primary);
        $this->assertIsBool($account->fresh()->is_primary);
    }

    #[Test]
    public function soft_deleted_accounts_are_hidden_from_default_queries(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = $ws->accounts()->create([
            'name' => 'A', 'type' => 'bank', 'status' => 'active', 'currency' => 'USD',
        ]);
        $account->delete();

        $this->assertSame(0, Account::count());
        $this->assertSame(1, Account::withTrashed()->count());
    }

    #[Test]
    public function factory_produces_a_valid_account_row(): void
    {
        $ws = HostWorkspace::create(['name' => 'Acme']);
        $account = Account::factory()->create([
            'owner_type' => HostWorkspace::class,
            'owner_id' => $ws->id,
        ]);

        $this->assertTrue($account->exists);
        $this->assertSame(3, strlen($account->currency));
        $this->assertNotNull($account->type);
    }
}
