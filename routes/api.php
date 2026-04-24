<?php

use Illuminate\Support\Facades\Route;
use Whilesmart\Accounts\Http\Controllers\AccountController;

Route::apiResource('accounts', AccountController::class);
Route::post('accounts/{account}/refresh-balance', [AccountController::class, 'refreshBalance'])
    ->name('accounts.refresh-balance');
