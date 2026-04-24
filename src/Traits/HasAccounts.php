<?php

namespace Whilesmart\Accounts\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Accounts\Models\Account;

/**
 * Add to models that own accounts (workspaces, organisations, users).
 */
trait HasAccounts
{
    public function accounts(): MorphMany
    {
        return $this->morphMany(Account::class, 'owner');
    }

    public function primaryAccount(?string $currency = null): ?Account
    {
        $query = $this->accounts()->where('is_primary', true);

        if ($currency) {
            $query->where('currency', $currency);
        }

        return $query->first();
    }
}
