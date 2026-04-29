<?php

namespace Goloba\GolobaRMA\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo al SELLER cuando un cliente crea una nueva solicitud RMA.
 */
class NewRequestSeller extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function build(): static
    {
        return $this
            ->to($this->data['seller_email'])
            ->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
            ->subject(trans('goloba-rma::app.mail.new-request.subject-seller', [
                'order_id' => $this->data['order_id'],
            ]))
            ->view('goloba-rma::emails.new-request-seller')
            ->with('data', $this->data);
    }
}
