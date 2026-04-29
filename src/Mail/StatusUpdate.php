<?php

namespace Goloba\GolobaRMA\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de cambio de estado RMA.
 * Se usa tanto para cliente como para seller: el $data['email'] y $data['body']
 * se resuelven en el controlador antes de instanciar.
 */
class StatusUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function build(): static
    {
        return $this
            ->to($this->data['email'])
            ->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
            ->subject(trans($this->data['subject_key'], [
                'rma_id'   => $this->data['rma_id'],
                'order_id' => $this->data['order_id'],
            ]))
            ->view('goloba-rma::emails.status-update')
            ->with('data', $this->data);
    }
}
