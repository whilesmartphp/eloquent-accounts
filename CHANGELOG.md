# Changelog

All notable changes to `whilesmart/eloquent-accounts` are documented here.

## [1.0.0] - 2026-06-12

- Initial release
- `Account` model with polymorphic `owner`
- `HasAccounts` trait for owner-side models (workspaces, organisations, users)
- `Account` contract -- marker interface for any model used on the account side of a payment or expense morph
- `AccountType` enum: bank, mobile_money, card_processor, wallet, cash, crypto, other
- `AccountStatus` enum: active, closed, frozen
- `primaryAccount(?string $currency = null)` helper on the owner
- `computeBalanceCents()` that derives balance from `opening_balance_cents` plus succeeded inbound payments, minus succeeded outbound payments, minus paid expenses. Gracefully degrades when sibling packages aren't installed.
- `refreshBalance()` persists the computed balance to `balance_cents`
- Auto-registered API routes: `apiResource accounts` + `POST accounts/{id}/refresh-balance`
- Config flag `compute_balance` to choose computed vs stored balance
- Factory for testing
- Publishable config (`accounts-config`) and migrations (`accounts-migrations`)
