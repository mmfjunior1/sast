<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RedefineSenha extends Mailable
{
    use Queueable, SerializesModels;

    public $view = 'layout.email.redefine-senha';
    public $valorLance;
    public $subject = 'Nova senha de acesso';
    public $user;
    public $senha;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $senha, $subject = 'Nova senha de acesso')
    {
        $this->subject = $subject;
        $this->senha = $senha;
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
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                     ->subject($this->subject)
                    ->view($this->view)
                    ->with([
                        'nome' => $this->user->nome,
                        'senha' => $this->senha,
                    ]);
    }
}
