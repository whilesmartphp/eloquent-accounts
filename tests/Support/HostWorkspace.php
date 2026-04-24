<?php

namespace Tests\Support;

use Illuminate\Database\Eloquent\Model;
use Whilesmart\Accounts\Traits\HasAccounts;

class HostWorkspace extends Model
{
    use HasAccounts;

    protected $table = 'workspaces';

    protected $guarded = [];
}
