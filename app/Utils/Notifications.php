<?php

namespace App\Utils;

use App\Utils\SendMail;
use App\User;

class Notifications extends SendMail
{
    public function posCad($nome, $email, $senha)
    {
        $text_body = 'Prezado(a) Senhor(a) '. $nome .', seu cadastro foi efetuado com sucesso no site da Globo Leilões!<br><br>';
        $text_body .= 'Seus dados para acessar ao site são:<br>';
        $text_body .= 'Login: '.$email.'<br>';
        $text_body .= 'Senha: '.$senha.'<br><br>';
        $text_body .= 'Antes de se habilitar para ofertar lances em nossos leilões, você deve fazer seu login em nosso site, clicar no item "minha conta" e anexar os seguintes documentos (tamanho máximo de 2mb por arquivo):<br><br>';
        $text_body .= '- Cópia simples do RG e CPF ou CNH<br>';
        $text_body .= '- Comprovante de residência (qualquer conta de consumo recente)<br><br>';
        $text_body .= 'Após enviar seus documentos você receberá um e-mail de nosso parceiro "clicksign" com um documento denominado "Declarações para Participar em Leilões". Ao clicar em "Ver Documento" você será direcionado para o site da clicksign para assinar digitalmente essa declaração.<br><br>';
        $text_body .= 'Assim que você assinar digitalmente a "Declarações para Participar em Leilões" no site da "clicksign" você estará apto para participar dos nossos leilões.<br><br>';
        $text_body .= 'Atenciosamente,<br>';
        $text_body .= 'Globo Leilões - Especialista em Imóveis<br>';
        $text_body .= 'Tel: 55 11 5503 6520 / 11 9-8796 8206<br>';
        $text_body .= 'Av. das Nações Unidas, 12.995 - 10º andar<br>';
        $text_body .= 'Brooklin Novo - CEP: 04578-000 - São Paulo / SP<br>';
        $text_body .= 'www.globoleiloes.com.br';
        $this->view = 'layout.email.blank';
        $this->send($email, ['body' => $text_body], 'Seu cadastro foi efetuado com sucesso');

        $text_body = 'O cliente ' . $nome . ', acaba de se cadastrar no site com o email '.$email.'!<br>';
        $text_body .= 'Acesse o gerenciador do site para visualizar seus dados.';

        $adminEmails = User::select('email')->where('status', '=', '1')
                             ->whereNotNull('email')->get()->toArray();
        $emails = [];
        foreach ($adminEmails  as $key => $value) {
            $emails[] = $value;
        }
        $this->view = 'layout.email.blank';
        $this->send($emails, ['body' => $text_body], 'Novo cadastro realizado');
    }

}