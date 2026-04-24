<?php

namespace Whilesmart\Accounts\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Frozen = 'frozen';
}
