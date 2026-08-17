<?php

return [
    'register_routes' => env('ACCOUNTS_REGISTER_ROUTES', true),
    'route_prefix' => env('ACCOUNTS_ROUTE_PREFIX', 'api'),
    'route_middleware' => ['api', 'auth:sanctum'],
    'table' => env('ACCOUNTS_TABLE', 'accounts'),

    // When true, GET /api/accounts/{account} computes balance_cents on the fly
    // from opening_balance_cents plus linked payments (inbound - outbound) and
    // minus paid expenses. Set false to rely only on the stored column.
    'compute_balance' => env('ACCOUNTS_COMPUTE_BALANCE', true),
    'ledger_balance' => env('ACCOUNTS_LEDGER_BALANCE', true),
];
