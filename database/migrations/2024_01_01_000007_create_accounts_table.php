<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('accounts.table', 'accounts'), function (Blueprint $table) {
            $table->id();

            // Who owns the account (workspace, organisation, user).
            $table->morphs('owner');

            // Human identity
            $table->string('name');
            $table->string('type')->default('bank');
            $table->string('status')->default('active');

            // Provider / institution details. Free-form strings at DB level so
            // new rails don't need migrations. See AccountType enum for
            // canonical type values.
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->string('identifier')->nullable();

            // Money
            $table->string('currency', 3);
            $table->bigInteger('opening_balance_cents')->default(0);
            $table->bigInteger('balance_cents')->default(0);

            $table->boolean('is_primary')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_type', 'owner_id', 'status']);
            $table->index(['owner_type', 'owner_id', 'type']);
            $table->unique(['owner_type', 'owner_id', 'provider', 'provider_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('accounts.table', 'accounts'));
    }
};
