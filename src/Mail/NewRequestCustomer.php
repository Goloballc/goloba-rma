<?php

namespace Goloba\GolobaRMA\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo al CLIENTE cuando crea una nueva solicitud RMA.
 * Complementa al CustomerRmaCreationEmail del vendor, que también se sigue enviando.
 * Este agrega la lógica de Goloba (tipo retracto/estándar, textos en español).
 */
class NewRequestCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function build(): static
    {
        return $this
            ->to($this->data['email'])
            ->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
            ->subject(trans('goloba-rma::app.mail.new-request.subject-customer', [
                'rma_id' => $this->data['rma_id'],
            ]))
            ->view('goloba-rma::emails.new-request-customer')
            ->with('data', $this->data);
    }
}
