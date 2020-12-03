<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CadastroSite;
use App\Cadastros;
use App\Utils\Notifications;
use Illuminate\Support\Facades\Mail;
use App\Mail\CadastroUsuario;
use App\Mail\NotificaAdms;
use Auth;

class CadastroSiteController extends Controller
{
    /**
     * List all profiles.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function index($tipo)
    {
        return view('site.cadastro.pessoa-'.$tipo);
    }

    /**
     * Update the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function save(CadastroSite $request, $tipo = null)
    {
        $input = $request->all();
        $tipo = $tipo == 'fisica' ? 1 : 2;
        
        $cadastro = new Cadastros();
        $cadastro->data_cadastro = date('Y-m-d') ;
        
        $cadastro->pessoa     = $tipo;
        if ($tipo == 1) {
            $cadastro->nome       = $input['nome'];
            $cadastro->cpf        = $input['cpf'];
            $cadastro->rg         = '' . $input['rg'] . '';
            $cadastro->filiacao   = $input['filiacao'];
            $cadastro->profissao  = $input['profissao'];
            $cadastro->empregador = $input['empregador'];
            $cadastro->data_nascimento = formataData($input['data_nascimento']);
            $cadastro->sexo            = $input['sexo'];
            $cadastro->estado_civil    = $input['estado_civil'];
            $cadastro->tipo_uniao      = $input['tipo_uniao'] ??  NULL;
            $cadastro->conjuge         = $input['conjuge'] ??  NULL;
            $cadastro->c_cpf           = $input['c_cpf'] ??  NULL;
            $cadastro->c_rg            = $input['c_rg'] ??  NULL;
        }
        if ($tipo == 2) {
            $cadastro->razao_social    = $input['razao_social'];
            $cadastro->rg              = '';
            $cadastro->cnpj            = $input['cnpj'];
            $cadastro->insc_estadual   = $input['insc_estadual'];
            $cadastro->nome_fantasia   = $input['nome_fantasia'];
            $cadastro->faturamento     = $input['faturamento'];
            $cadastro->segmento        = $input['segmento'];
            $cadastro->socio           = $input['socio'];
            $cadastro->s_cpf           = $input['s_cpf'];
            $cadastro->s_rg            = $input['s_rg'];
        }
        $cadastro->email           = $input['email'];
        $cadastro->telefone        = $input['telefone'];
        $cadastro->celular         = $input['celular'];
        $cadastro->apelido = trim ($input['apelido']);
        $cadastro->apelido = str_replace(['@', ' '], '', $cadastro->apelido);
        $cadastro->cep             = $input['cep'];
        $cadastro->endereco        = $input['endereco'];
        $cadastro->numero          = $input['numero'];
        $cadastro->complemento     = $input['complemento'];
        $cadastro->bairro          = $input['bairro'];
        $cadastro->cidade          = $input['cidade'];
        $cadastro->estado          = $input['estado'];
        $cadastro->desc_como_chegou = $input['desc_como_chegou'];
        $senha                     = uniqid();
        $cadastro->senha           = md5($senha);
       
        $cadastro->save();
        Mail::to($cadastro->email)
        ->queue(new CadastroUsuario($cadastro, $senha, ''));
        $nome = $tipo == 2 ? $input['razao_social'] : $input['nome'];
        $texto  = 'O cliente ' . $nome . ', acaba de se cadastrar no site com o email ' . $input['email'] . '!<br>';
        Mail::cc(getAdmEmails())
        ->queue(new NotificaAdms('Novo usuário cadastrado', $texto));
        return redirect()->back()->with('success', 'Seu cadastro foi efetuado com sucesso. Verifique seu email para obtenção da senha.');
    }

    public function verificaApelido(Request $request) {
        $request->apelido = str_replace('@', '', $request->apelido);
        $apelido = Cadastros::where('apelido', '=', trim($request->apelido));
        $user = Auth::user();
        if ($user) {
            $apelido = $apelido->where('codigo', '!=', $user->codigo);
        }
        if ($apelido->count() > 0) {
            return ['status' => false];
        }
        return ['status' => true];
    }
}