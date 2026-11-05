<?php

namespace App\Models\Concerns;

trait HasPaymentStatus
{
    public const PAYMENT_STATUS_PENDING = '0';
    public const PAYMENT_STATUS_APPROVED = '1';
    public const PAYMENT_STATUS_PROCEED = '2';

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_APPROVED => 'Approved',
            self::PAYMENT_STATUS_PROCEED => 'Proceed',
        ];
    }

    public function getPaymentStatusBadgeAttribute()
    {
        return match ((string) $this->payment_status) {
            self::PAYMENT_STATUS_APPROVED => '<span class="badge bg-success">Approved</span>',
            self::PAYMENT_STATUS_PROCEED => '<span class="badge bg-info">Proceed</span>',
            default => '<span class="badge bg-secondary">Pending</span>',
        };
    }
}
