<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\FacaSeuLeilao;
use App\TiposLeilao;
use App\Utils\SendMail;

class FacaSeuLeilaoController extends Controller
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Show the text.
     *
     * @param  int  $id
     * @return View
     */
    public function index()
    {
        $tiposLeilao = TiposLeilao::all();
        
        return view('site.faca-seu-leilao', ['texto' => $tiposLeilao]);
    }

    public function enviaProspect(FacaSeuLeilao $request)
    {
        $request->validated();

        $recap = 'g-recaptcha-response';
        $resposta = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Leo7-sUAAAAABqIHy9uY8klzyRNYxqCpzSORGYs&response=".$request->$recap."&remoteip=".$_SERVER['REMOTE_ADDR']);
        $resposta = json_decode($resposta);

        if (!$resposta->success) {
            return response()->json([
                'status' => 'false'
            ], 503);
        }
        $array = [];
        $array['nome'] = $request->nome;
        $array['email'] = $request->email;
        $array['tipo'] = (int) $request->tipo_leilao;
        $array['assunto'] = 'Solicitação de leilão ';
        $array['processo'] = $request->processo;
        $array['dados_imovel'] = $request->dados_imovel;
        $array['telefone'] = $request->telefone;
        $tipo = $array['tipo'] == 1 ? '<tr><td><strong>Tipo:</strong> Solicitação de Leilão Judicial</td></tr><tr><td><strong>Processo:</strong>'.$array['processo'].' </td></tr>' : '<tr><td><strong>Tipo:</strong> Solicitação de Leilão Extrajudicial</td></tr><tr><td><strong>Dados do imóvel:</strong> '.$array['dados_imovel'].'</td></tr>';
        $mensagem = '<p>Foi registrado um pedido de leilão. Abaixo, os dados do pedido:</p>';
        $mensagem .= '<table>
    <tr>
    <td><strong>Nome:</strong> '.$array['nome'].'</td></tr>
    <tr><td><strong>E-mail:</strong> '.$array['email'].'</td></tr>
    <tr><td><strong>Telefone:</strong> '.$array['telefone'].'</td></tr>
    '. $tipo .'    
</table>';
        Mail::cc(['mmfjunior1@gmail.com','contato@globoleiloes.com.br', 'regischagas@gmail.com', 'rose@globoleiloes.com.br'])
            ->queue(new EmailGenerico('Solicitação de leilão', $mensagem));
        return response()->json([
            'status' => 'true'
        ]);
    }

}