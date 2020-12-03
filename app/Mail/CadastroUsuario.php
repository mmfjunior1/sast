<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Cadastros;
use App\ConfigEmail;

class CadastroUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public $view = 'layout.email.confirmacao-de-cadastro';
    public $cadastros;
    private $senha;

    

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Cadastros $cadastros, $senha, $view = '')
    {
        if ($view != '') {
            $this->view = $view;
        }
        $this->cadastros = $cadastros;
        $this->senha = $senha;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $corpoEmail = ConfigEmail::where('email_tipo', 'confirma_cadastro')->first();

        $emailFrom = isset($arrayVars['emailFrom']) ? $arrayVars['emailFrom'] : env('MAIL_FROM_ADDRESS');
        $fromName = isset($arrayVars['fromName']) ? $arrayVars['fromName'] : env('MAIL_FROM_NAME');
        $apelido = $this->cadastros->apelido;
        $nome = $this->cadastros->nome;
        $email = $this->cadastros->email;
        $senha = $this->senha;

        $arr = array('{{str_nome}}' => $nome, '{{email}}' => $email, '{{senha}}' => $senha, '{{apelido}}' => $apelido);
        
        $corpoEmail = strtr($corpoEmail->texto, $arr);

        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->view($this->view)
                    ->with([
                        'corpoEmail' => $corpoEmail,
                    ]);
    }
}
