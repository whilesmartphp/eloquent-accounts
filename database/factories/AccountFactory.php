<?php

namespace Whilesmart\Accounts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Whilesmart\Accounts\Enums\AccountStatus;
use Whilesmart\Accounts\Enums\AccountType;
use Whilesmart\Accounts\Models\Account;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company().' account',
            'type' => $this->faker->randomElement([
                AccountType::Bank->value,
                AccountType::MobileMoney->value,
                AccountType::Wallet->value,
            ]),
            'status' => AccountStatus::Active->value,
            'provider' => $this->faker->randomElement(['UBA', 'MTN MoMo', 'Orange Money', 'Stripe', 'Paystack']),
            'provider_reference' => 'acc_'.$this->faker->unique()->bothify('??####'),
            'identifier' => $this->faker->bothify('****####'),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'XAF', 'NGN', 'KES']),
            'opening_balance_cents' => $this->faker->numberBetween(0, 100_000_00),
            'balance_cents' => 0,
            'is_primary' => false,
        ];
    }
}
