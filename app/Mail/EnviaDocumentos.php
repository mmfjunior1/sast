<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnviaDocumentos extends Mailable
{
    use Queueable, SerializesModels;

    public $view = 'layout.email.aviso-documentos';
    public $user;
    

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $view = '')
    {
        if ($view != '') {
            $this->view = $view;
        }
        $this->user = $user;
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
        $nome = trim($this->user->nome) != '' ? $this->user->nome : $this->user->razao_social;
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject('Envio de Documentos')
                    ->view($this->view)
                    ->with([
                        'nome' => $nome,
                    ]);
    }
}
