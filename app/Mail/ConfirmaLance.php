<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\ConfigEmail;

class ConfirmaLance extends Mailable
{
    use Queueable, SerializesModels;

    public $view = 'layout.email.confirma-lance';
    public $valorLance;
    public $subject = 'Aprova documentos';
    public $titulo;
    public $tipo;
    public $user;
    public $compraParcelada = false;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $view = '', $subject = 'Confirmamos seu lance!', $valorLance = 0, $titulo = '', $tipo = 0)
    {
        if ($view != '') {
            $this->view = $view;
        }
        $this->valorLance = $valorLance;
        $this->titulo = $titulo;
        $this->subject = $subject;
        $this->tipo = $tipo;
        $this->user = $user;
        if ($this->view == 'confirma-compra-parcelada') {
            $this->compraParcelada = true;
        }
        if ($subject == 'Nova proposta de compra enviada!') {
            $this->compraParcelada = true;
        }
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
        
        $corpoEmail = '';
        
        if ($this->view == 'layout.email.confirma-lance') {
            $arr = array('{{st_nome}}' => trim($this->user->nome) != '' ? $this->user->nome : $this->user->razao_social,
					'{{valor_lance}}' => $this->valorLance,
                    '{{titulo}}' => $this->titulo,);

            $corpoEmail = ConfigEmail::where('email_tipo', 'recebe_lance')->first();
            $corpoEmail = strtr($corpoEmail->texto, $arr);
        }

        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                     ->subject($this->subject)
                    ->view($this->view)
                    ->with([
                        'nome' => trim($this->user->nome) != '' ? $this->user->nome : $this->user->razao_social,
                        'valorLance' => $this->valorLance,
                        'titulo' => $this->titulo,
                        'tipo' => $this->tipo,
                        'parcelada' => $this->compraParcelada,
                        'corpoEmail' => $corpoEmail
                    ]);
    }
}
