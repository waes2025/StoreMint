<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionPayment extends Model
{
    protected $fillable = [
        'transaction_id',
        'business_id',
        'is_return',
        'amount',
        'method',
        'payment_type',
        'transaction_no',
        'card_transaction_number',
        'card_number',
        'card_type',
        'card_holder_name',
        'card_month',
        'card_year',
        'card_security',
        'cheque_number',
        'bank_account_number',
        'paid_on',
        'created_by',
        'gateway',
        'is_advance',
        'is_rebate',
        'payment_for',
        'parent_id',
        'note',
        'document',
        'payment_ref_no',
        'account_id',
        'status',
        'currency',
        'gateway_response',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_on' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Determine the payment type based on the amount.
     * "If the amount is reasonable, then the payment_type is debit."
     */
    public static function determinePaymentType($amount): ?string
    {
        return $amount > 0 ? 'debit' : null;
    }

    /**
     * Public function to get valid payment statuses.
     */
    public static function getStatuses(): array
    {
        return ['initiated', 'success', 'failed', 'cancelled', 'refunded'];
    }

    /**
     * Public function to check if a status is valid.
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::getStatuses());
    }
}
