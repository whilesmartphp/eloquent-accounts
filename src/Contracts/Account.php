<?php

namespace Whilesmart\Accounts\Contracts;

/**
 * Marker interface for any model used on the account side of payments and
 * expenses. In most apps the concrete model is Whilesmart\Accounts\Models\Account,
 * but the morph fields accept anything that implements this interface.
 */
interface Account
{
}
