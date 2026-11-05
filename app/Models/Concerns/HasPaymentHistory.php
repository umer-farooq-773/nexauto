<?php

namespace App\Models\Concerns;

use App\Models\PaymentHistory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPaymentHistory
{
    /**
     * All payment history records for this incident.
     */
    public function paymentHistories(): MorphMany
    {
        return $this->morphMany(PaymentHistory::class, 'payable')->latest();
    }
}
