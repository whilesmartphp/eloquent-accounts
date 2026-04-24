<?php

namespace Whilesmart\Accounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:200'],
            'type' => ['nullable', 'in:bank,mobile_money,card_processor,wallet,cash,crypto,other'],
            'status' => ['nullable', 'in:active,closed,frozen'],
            'provider' => ['nullable', 'string', 'max:100'],
            'provider_reference' => ['nullable', 'string', 'max:200'],
            'identifier' => ['nullable', 'string', 'max:200'],
            'currency' => ['nullable', 'string', 'size:3'],
            'opening_balance_cents' => ['nullable', 'integer'],
            'balance_cents' => ['nullable', 'integer'],
            'is_primary' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
