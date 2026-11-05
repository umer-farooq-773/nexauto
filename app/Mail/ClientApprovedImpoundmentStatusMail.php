<?php

namespace App\Mail;

use App\Models\Impoundment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientApprovedImpoundmentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Impoundment $impoundment
    ) {
    }

    public function build(): self
    {
        $label = $this->impoundment->status == '1' ? 'Released' : 'Impounded';

        return $this
            ->subject('Client has approved. Now you can change the status.')
            ->view('emails.client_approved_impoundment_status')
            ->with([
                'impoundment' => $this->impoundment,
                'currentLabel' => $label,
            ]);
    }
}

