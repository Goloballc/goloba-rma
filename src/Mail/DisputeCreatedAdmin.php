<?php

namespace Goloba\GolobaRMA\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo al ADMIN cuando un seller abre una disputa.
 */
class DisputeCreatedAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function build(): static
    {
        return $this
            ->to($this->data['admin_email'])
            ->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
            ->subject(trans('goloba-rma::app.mail.dispute-created.subject-admin', [
                'rma_id' => $this->data['rma_id'],
            ]))
            ->view('goloba-rma::emails.dispute-created-admin')
            ->with('data', $this->data);
    }
}
