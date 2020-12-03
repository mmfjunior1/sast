<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class ComunicaVencedor extends Mailable
{
    use Queueable, SerializesModels;

    public $view = 'layout.email.email-generico';
    public $texto;
    public $subject = 'Aprova documentos';
    public $cadastros;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject = 'Aprova documentos', $texto)
    {
        $this->subject = $subject;
        $this->texto = $texto;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $emailFrom = isset($arrayVars['emailFrom']) ? $arrayVars['emailFrom'] : env('MAIL_FROM_ADDRESS');
        $fromName = isset($arrayVars['fromName']) ? $arrayVars['fromName'] : env('MAIL_FROM_NAME');
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                     ->subject($this->subject)
                    ->view($this->view)
                    ->with([
                        'html' => $this->texto,
                    ]);
    }
}
