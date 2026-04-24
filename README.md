# Eloquent Accounts

Polymorphic financial account records for Laravel. Bank accounts, mobile money wallets, card processors, cash registers, crypto wallets. Attaches on the account side of `whilesmart/eloquent-payments` and `whilesmart/eloquent-expenses`.

## Why

`eloquent-payments` and `eloquent-expenses` both carry a nullable `account` polymorph. Without a concrete account model those fields go unused; reporting can't answer "what's in the Lagos UBA account right now?" or "how much came in through MTN MoMo this month?"

This package fills that gap. It is optional: if you don't install it, the morph columns on payments / expenses stay null and nothing breaks.

## Install

```
composer require whilesmart/eloquent-accounts
php artisan migrate
```

Attach `HasAccounts` to the model that owns accounts (typically a workspace):

```php
use Whilesmart\Accounts\Traits\HasAccounts;

class Workspace extends Model
{
    use HasAccounts;
}
```

## Data model

One `accounts` table. Each row belongs polymorphically to an owner and holds:

- **Identity**: `name`, `type` (`bank | mobile_money | card_processor | wallet | cash | crypto | other`), `status` (`active | closed | frozen`).
- **Provider**: `provider` (string, e.g. `UBA`, `MTN MoMo`, `Stripe`), `provider_reference`, `identifier` (account number / last 4 / wallet number, safe to display).
- **Money**: `currency`, `opening_balance_cents`, `balance_cents`.
- **`is_primary`**: default account for its owner + currency. Helper `primaryAccount(string $currency = null)` on the owner.
- **`metadata`**: JSON.

`type` and `provider` are free-form strings at the DB level so new rails don't need migrations; `AccountType` enum carries the canonical values.

## Balance

Two modes:

1. **Computed** (default, `ACCOUNTS_COMPUTE_BALANCE=true`): when you read an account, `balance_cents` is derived as `opening_balance_cents + succeeded inbound payments - succeeded outbound payments - paid expenses`. Requires `whilesmart/eloquent-payments` and/or `whilesmart/eloquent-expenses`; gracefully degrades if they are not installed.
2. **Stored** (`ACCOUNTS_COMPUTE_BALANCE=false`): reads the `balance_cents` column. Call `$account->refreshBalance()` (or `POST /api/accounts/{id}/refresh-balance`) to recompute and persist.

## Routes

```
GET    /api/accounts
POST   /api/accounts
GET    /api/accounts/{account}
PUT    /api/accounts/{account}
DELETE /api/accounts/{account}
POST   /api/accounts/{account}/refresh-balance
```

Index filters: `owner_type`, `owner_id`, `type`, `status`, `currency`, `q`, `per_page`.

## Config

`php artisan vendor:publish --tag=accounts-config`:

```php
return [
    'register_routes' => env('ACCOUNTS_REGISTER_ROUTES', true),
    'route_prefix' => env('ACCOUNTS_ROUTE_PREFIX', 'api'),
    'route_middleware' => ['api', 'auth:sanctum'],
    'table' => env('ACCOUNTS_TABLE', 'accounts'),
    'compute_balance' => env('ACCOUNTS_COMPUTE_BALANCE', true),
];
```

## Siblings

- `whilesmart/eloquent-payments` -- inbound / outbound transfers. Each Payment has `account_type` + `account_id`.
- `whilesmart/eloquent-expenses` -- expenses, paid from accounts.
- `whilesmart/eloquent-invoices` -- invoices (money in). Tracks summary totals on the invoice row; actual payment records live in `eloquent-payments`, which reference an Account.
