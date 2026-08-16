<?php

namespace Whilesmart\Accounts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Whilesmart\Accounts\Contracts\Account as AccountContract;
use Whilesmart\Accounts\Database\Factories\AccountFactory;
use Whilesmart\Accounts\Enums\AccountStatus;
use Whilesmart\Accounts\Enums\AccountType;
use Whilesmart\Expenses\Models\Expense;
use Whilesmart\Payments\Models\Payment;
use Whilesmart\Transactions\Models\Transaction;

class Account extends Model implements AccountContract
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'type' => AccountType::class,
        'status' => AccountStatus::class,
        'is_primary' => 'boolean',
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        return config('accounts.table', 'accounts');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Payments routed through this account. Requires whilesmart/eloquent-payments.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'account');
    }

    /**
     * Expenses paid from this account. Requires whilesmart/eloquent-expenses.
     */
    public function expenses(): MorphMany
    {
        return $this->morphMany(Expense::class, 'account');
    }

    /**
     * Ledger movements on this account. Requires whilesmart/eloquent-transactions.
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'account');
    }

    /**
     * Compute the balance from opening_balance_cents and account movements.
     * When the transactions ledger is installed it is the single source of
     * movement (payments and expenses post into it), so its posted entries
     * are summed and the legacy payment/expense terms are skipped to avoid
     * double counting. Without the ledger, falls back to succeeded payments
     * minus paid expenses. Degrades gracefully when siblings aren't installed.
     */
    public function computeBalanceCents(): int
    {
        $balance = (int) $this->opening_balance_cents;

        if (class_exists(Transaction::class)) {
            $balance += (int) $this->transactions()
                ->where('status', 'posted')
                ->where('direction', 'credit')
                ->sum('amount_cents');

            $balance -= (int) $this->transactions()
                ->where('status', 'posted')
                ->where('direction', 'debit')
                ->sum('amount_cents');

            return $balance;
        }

        if (class_exists(Payment::class)) {
            $balance += (int) $this->payments()
                ->where('status', 'succeeded')
                ->where('direction', 'inbound')
                ->sum('amount_cents');

            $balance -= (int) $this->payments()
                ->where('status', 'succeeded')
                ->where('direction', 'outbound')
                ->sum('amount_cents');
        }

        if (class_exists(Expense::class)) {
            $balance -= (int) $this->expenses()
                ->where('status', 'paid')
                ->sum('total_cents');
        }

        return $balance;
    }

    public function refreshBalance(): self
    {
        $this->balance_cents = $this->computeBalanceCents();
        $this->save();

        return $this;
    }

    protected static function newFactory(): AccountFactory
    {
        return AccountFactory::new();
    }
}
