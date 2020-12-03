<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class Prospect extends Mailable
{
    use Queueable, SerializesModels;

    public $view = 'layout.email.prospect';
    public $subject = 'Solicitação de leilão';
    public $arrayDados;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject = 'Solicitação de leilão', $arrayDados)
    {
        $this->subject = $subject;
        $this->arrayDados = $arrayDados;
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
                    ->with($this->arrayDados);
    }
}
