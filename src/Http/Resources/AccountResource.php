<?php

namespace Whilesmart\Accounts\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'identifier' => $this->identifier,
            'currency' => $this->currency,
            'opening_balance_cents' => $this->opening_balance_cents,
            'balance_cents' => $this->when(
                config('accounts.compute_balance', true),
                fn () => $this->resource->computeBalanceCents(),
                $this->balance_cents,
            ),
            'is_primary' => $this->is_primary,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
