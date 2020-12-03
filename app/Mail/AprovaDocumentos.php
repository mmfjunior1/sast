<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Cadastros;
use App\ConfigEmail;

class AprovaDocumentos extends Mailable
{
    use Queueable, SerializesModels;

    public $view = 'layout.email.aprova-documento-pessoal';
    public $subject = 'Aprova documentos';
    public $cadastros;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Cadastros $cadastros, $view = '', $subject = 'Aprova documentos')
    {
        if ($view != '') {
            $this->view = $view;
        }
        $this->subject = $subject;
        $this->cadastros = $cadastros;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->view == 'layout.email.aprova-todos-documentos') {
            $corpoEmail = ConfigEmail::where('email_tipo', 'cadastro_documentos')->where('id', '=', 43) ->first();
        }

        if ($this->view == 'layout.email.aprova-documento-pessoal') {
            $corpoEmail = ConfigEmail::where('email_tipo', 'cadastro_documentos')->where('id', '=', 41) ->first();
        }

        if ($this->view == 'layout.email.aprova-documento-residencia') {
            $corpoEmail = ConfigEmail::where('email_tipo', 'cadastro_documentos')->where('id', '=', 42) ->first();
        }

        $arr = array('{{nome}}' => trim($this->cadastros->nome) != '' ? $this->cadastros->nome : $this->cadastros->razao_social,
					);


        $corpoEmail = strtr($corpoEmail->texto, $arr);

        $emailFrom = isset($arrayVars['emailFrom']) ? $arrayVars['emailFrom'] : env('MAIL_FROM_ADDRESS');
        $fromName = isset($arrayVars['fromName']) ? $arrayVars['fromName'] : env('MAIL_FROM_NAME');
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                     ->subject($this->subject)
                    ->view('layout.email.aprova-documento')
                    ->with([
                        'nome' => $this->cadastros->nome,
                        'corpoEmail' => $corpoEmail
                    ]);
    }
}
