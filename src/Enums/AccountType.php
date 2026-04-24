<?php

namespace Whilesmart\Accounts\Enums;

/**
 * Canonical account types. The `type` column is a free-form string at the DB
 * level so providers can introduce new rails without migrations; use these
 * values when the type is known up front.
 */
enum AccountType: string
{
    case Bank = 'bank';
    case MobileMoney = 'mobile_money';
    case CardProcessor = 'card_processor';
    case Wallet = 'wallet';
    case Cash = 'cash';
    case Crypto = 'crypto';
    case Other = 'other';
}
