<?php

namespace Whilesmart\Accounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerRequest;

class StoreAccountRequest extends FormRequest
{
    use AuthorizesOwnerRequest;

    public function authorize(): bool
    {
        return $this->authorizeOwnerInRequest();
    }

    public function rules(): array
    {
        return [
            'owner_type' => ['required', 'string'],
            'owner_id' => ['required'],
            'name' => ['required', 'string', 'max:200'],
            'type' => ['nullable', 'in:bank,mobile_money,card_processor,wallet,cash,crypto,other'],
            'status' => ['nullable', 'in:active,closed,frozen'],
            'provider' => ['nullable', 'string', 'max:100'],
            'provider_reference' => ['nullable', 'string', 'max:200'],
            'identifier' => ['nullable', 'string', 'max:200'],
            'currency' => ['required', 'string', 'size:3'],
            'opening_balance_cents' => ['nullable', 'integer'],
            'balance_cents' => ['nullable', 'integer'],
            'is_primary' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
