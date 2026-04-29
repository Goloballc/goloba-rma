<?php

namespace Goloba\GolobaRMA\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de resolución de disputa.
 * Se usa para seller y para cliente: el $data['email'], $data['body'] y
 * $data['subject_key'] se resuelven en el controlador antes de instanciar.
 */
class DisputeResolved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function build(): static
    {
        return $this
            ->to($this->data['email'])
            ->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
            ->subject(trans($this->data['subject_key'], [
                'rma_id' => $this->data['rma_id'],
            ]))
            ->view('goloba-rma::emails.dispute-resolved')
            ->with('data', $this->data);
    }
}
