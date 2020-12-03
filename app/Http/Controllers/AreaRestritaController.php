<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CadastroSite;
use App\CadastrosDocumentos;
use App\Cadastros;
use App\Leiloes;
use App\Lances;
use App\Mail\EnviaDocumentos;
use Illuminate\Support\Facades\Mail;
use Auth;

class AreaRestritaController extends Controller
{
    public function index() {
        return view('site.area-restrita.minha-conta');
    }

    public function editaCadastro() {
        return view('site.area-restrita.edicao-cadastro', ['vet' => Auth::user()->toArray()]);
    }

    public function editCad(CadastroSite $request) {
        $input = $request->all();
        $cadastro = \Auth::user();

        if ($cadastro->pessoa == 1) {
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
        if ($cadastro->pessoa == 2) {
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
        if ($input['email'] != $cadastro->email) {
            $cadastro->email           = $input['email'];
        }
        $cadastro->telefone        = $input['telefone'];
        $cadastro->celular         = $input['celular'];
        $cadastro->apelido = trim ($input['apelido']);
        $cadastro->apelido = str_replace(['@', ''], '', $cadastro->apelido);
        $cadastro->cep             = $input['cep'];
        $cadastro->endereco        = $input['endereco'];
        $cadastro->numero          = $input['numero'];
        $cadastro->complemento     = $input['complemento'];
        $cadastro->bairro          = $input['bairro'];
        $cadastro->cidade          = $input['cidade'];
        $cadastro->estado          = $input['estado'];
        $cadastro->como_chegou     = (int) $input['como_chegou'];
        $cadastro->save();
        return redirect('/meus-dados')->with('success' , 'Cadastro editado com sucesso!');
    }

    public function changePassWord(Request $request) {
        $input = $request->all();
        $user = Auth::user();
        if ($user->changed_password != 1) {
            if ($user->senha == md5($input['senha_atual'])) {
                if ($input['senha_nova'] != $input['confirm_senha_nova']) {
                    return redirect('/meus-dados')->withErrors(['msg' => 'Novas senhas informadas não conferem!']);        
                }
                $user->senha = \Hash::make($input['senha_nova']);
                $user->changed_password = 1;
                $user->save();
                return redirect('/minha-conta')->with('success' , 'Sua senha foi alterada com sucesso.');
            }
            if (\Hash::check($input['senha_atual'], $user->senha)) {
                $user->senha = \Hash::make($input['senha_nova']);
                $user->changed_password = 1;
                $user->save();
                return redirect('/minha-conta')->with('success' , 'Sua senha foi alterada com sucesso.');
            }
            return redirect('/meus-dados')->withErrors(['msg' => 'Senha atual informada está incorreta!']);
        }

        if (\Hash::check($input['senha_atual'], $user->senha)) {
            if ($input['senha_nova'] != $input['confirm_senha_nova']) {
                return redirect('/meus-dados')->withErrors(['msg' => 'Novas senhas informadas não conferem!']);        
            }
            $user->senha = \Hash::make($input['senha_nova']);
            $user->changed_password = 1;
            $user->save();
            return redirect('/minha-conta')->with('success' , 'Sua senha foi alterada com sucesso.');
        }
        return redirect('/meus-dados')->withErrors(['msg' => 'Senha atual informada está incorreta!']);
    }

    public function salvaDocumento(Request $request) {
        if (!$request->arq_pessoal || !$request->arq_residencia) {
            return redirect('/meus-dados')->withErrors(['msg' => 'Você deve enviar os dois comprovantes solicitados!']);
        }
        
        $idUsuario = Auth::user()->codigo;

        $arqName1 = $request->arq_pessoal->getClientOriginalName();
        $arqName1 = trim ($arqName1);
        if ($arqName1 == '') {
            return redirect('/meus-dados')->withErrors(['msg' => 'Algo deu errado. Os documentos não foram armazenados. Tente novamente!']);
        }
        $ext = $request->arq_pessoal->getClientOriginalExtension();
        $arqName1 = str_replace([' ', '/', '\\', '(', ')', '=', '+', '_'], '-', $arqName1);
        $arqName1 = str_replace('.'.$ext, '', $arqName1);
        $arqName1 = $arqName1 . '-' . md5($arqName1 . time() . $idUsuario) . '.' . $ext;

        
        $arqName2 = $request->arq_residencia->getClientOriginalName();
        $arqName2 = trim ($arqName2);
        if ($arqName2 == '') {
            return redirect('/meus-dados')->withErrors(['msg' => 'Algo deu errado. Os documentos não foram armazenados. Tente novamente!']);
        }
        $ext = $request->arq_residencia->getClientOriginalExtension();
        $arqName2 = str_replace([' ', '/', '\\', '(', ')', '=', '+', '_'], '-', $arqName2);
        $arqName2 = str_replace('.'.$ext, '', $arqName2);
        $arqName2 = $arqName2 . '-' . md5($arqName2 . time() . $idUsuario) . '.' . $ext;

        $request->arq_pessoal->storeAs('documentos', $arqName1);
        $request->arq_residencia->storeAs('documentos', $arqName2);
    
        CadastrosDocumentos::updateOrCreate(['idcadastro' => $idUsuario],
                                                [
                                                    'arq_residencia' => $arqName2,
                                                    'arq_pessoal' => $arqName1,
                                                ]
                                            );
                                            
        Mail::cc(getAdmEmails())
        ->queue(new EnviaDocumentos(Auth::user(), ''));
        return redirect('/minha-conta')->with('success' , 'Arquivo enviado com sucesso!');
    }

    public function atividades(Request $request) {
        $user = Auth::user();
        $habilitados = new Cadastros();
        $lotesHabilitados = $habilitados
                            ->with(
                                'habilitados', 
                                'lances', 
                                'arrematacao', 
                                'ultimoLanceAutomatico')
                            ->where('codigo', $user->codigo)->get();
        return view('site.area-restrita.area-restrita', ['habilitados' => $lotesHabilitados]);
    }

    public function alteraValorLanceAutomatico(Request $request) {
        $user = Auth::user();
        $lances = Lances::with('lote')->where('idcadastro', $user->codigo)
                        ->where('codigo', $request->codigo)
                        ->get();

        if ($lances->count() == 0) {
            return abort(404);
        }
    }
}