<?php

namespace Whilesmart\Accounts\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Whilesmart\Accounts\Http\Requests\StoreAccountRequest;
use Whilesmart\Accounts\Http\Requests\UpdateAccountRequest;
use Whilesmart\Accounts\Http\Resources\AccountResource;
use Whilesmart\Accounts\Models\Account;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Account::query();

        if ($request->filled('owner_type') && $request->filled('owner_id')) {
            $query->where('owner_type', $request->input('owner_type'))
                ->where('owner_id', $request->input('owner_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->input('currency'));
        }

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                    ->orWhere('provider', 'ilike', "%{$term}%")
                    ->orWhere('identifier', 'ilike', "%{$term}%");
            });
        }

        $accounts = $query->orderBy('is_primary', 'desc')
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => AccountResource::collection($accounts)->response()->getData(true),
        ]);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new AccountResource($account),
        ], 201);
    }

    public function show(Account $account): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new AccountResource($account),
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $account->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new AccountResource($account),
        ]);
    }

    public function destroy(Account $account): JsonResponse
    {
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted.',
        ]);
    }

    public function refreshBalance(Account $account): JsonResponse
    {
        $account->refreshBalance();

        return response()->json([
            'success' => true,
            'data' => new AccountResource($account),
        ]);
    }
}
