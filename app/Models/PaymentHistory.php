<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'original_cost',
        'paid_amount',
        'payment_method',
        'reference_number',
        'notes',
        'paid_to',
        'authority',
        'price',
        'processed_by',
        'processed_by_name',
        'expense_details'
    ];

    protected $casts = [
        'original_cost'    => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'payment_method'   => 'array',
        'reference_number' => 'array',
        'notes'            => 'array',
        'paid_to'          => 'array',
        'authority'        => 'array',
        'price'            => 'array',
        'expense_details'  => 'array'
    ];

    /**
     * Polymorphic relationship back to the incident model.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user who processed the payment.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Human-readable payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        $method = is_array($this->payment_method)
            ? implode(', ', $this->payment_method)
            : $this->payment_method;

        return match ($method) {
            'cash'          => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card'   => 'Credit Card',
            'cheque'        => 'Cheque',
            'pos_payment'   => 'POS Payment',
            'atm_transfer'  => 'ATM Transfer',
            'mobile_money'  => 'Mobile Money',
            default         => $method ?? '-',
        };
    }

    /**
     * Whether the paid amount differs from the original cost.
     */
    public function getCostWasAdjustedAttribute(): bool
    {
        if (is_null($this->original_cost) || is_null($this->paid_amount)) {
            return false;
        }

        return (float) $this->original_cost !== (float) $this->paid_amount;
    }
}
