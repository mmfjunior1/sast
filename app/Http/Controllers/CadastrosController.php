<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Cadastros;
use App\CadastrosDocumentos;
use App\Mail\AprovaDocumentos;
use App\Mail\EmailGenerico;
use App\UserSite;
use App\HistoricoContato;
use Auth;

class CadastrosController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        return view('admin.users.index', ['user' => User::findOrFail($id)]);
    }
    /**
     * List all profiles.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function index(Request $request)
    {
        $name = $request->route()->getName();
        $vet = Cadastros::all();
        if ($name == 'mailing') {
            return view('admin.mailing.mailing', ['cads' => $vet]);    
        }
        return view('admin.cads.index', ['cads' => $vet]);
    }

    /**
     * List all profiles.
     *
     * @param  Request  $request
     * @return redirect()
     */

    public function search(Request $request)
    {
        $input = $request->all();
        $cadastros = new Cadastros();
        $nome = trim($input['nome']);
        $cpfCnpj = trim($input['cpf_cnpj']);
        $status = (int) $input['status'];
        $dataInicial = trim($input['data_inicial']);
        $dataFinal = trim($input['data_final']);

        if ($nome != '') {
            $cadastros = $cadastros->where('nome', 'like', '%' . $nome . '%')
                                ->orWhere('razao_social', 'like ', '%' . $nome . '%');
        }

        if ($cpfCnpj != '') {
            $cadastros = $cadastros->where('cpf', 'like', '%' . $cpfCnpj . '%')
                                ->orWhere('cnpj', 'like ', '%' . $cpfCnpj . '%');
        }

        if ($status != 10) {
            $cadastros = $cadastros->where('status', '=',  $status );
        }

        if ($dataInicial != '' && $dataFinal != '') {
            $dataInicial = explode('/', $dataInicial);
            $dataFinal = explode('/', $dataFinal);
            $dataInicial = $dataInicial[2] . '-' . $dataInicial[1] . '-' . $dataInicial[0];
            $dataFinal = $dataFinal[2] . '-' . $dataFinal[1] . '-' . $dataFinal[0];
            $cadastros = $cadastros->whereBetween('data_cadastro', [$dataInicial, $dataFinal]);
        }
        $cadastros = $cadastros->get();
        
        return view('admin.cads.index', ['cads' => $cadastros, 'input' => $input]);
    }

    /**
     * Update the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function save(Request $request)
    {
        $input = $request->all();
        
        //enviar email
        // $str_nome = $input['razao_social'];
        // if($pessoa == 1)
        //     $str_nome = $input['nome'];
        //fim enviar email
        $cadastro = new Cadastros();
        $input['codigo'] = (int) $input['codigo'];
        $cadastro->data_cadastro = date('Y-m-d') ;
        if ($input['codigo'] > 0) {
            $cadastro = Cadastros::find($input['codigo']);
            unset($cadastro->data_cadastro);
        }
        $cadastro->pessoa     = $input['pessoa'];
        $cadastro->nome       = $input['nome'];
        $cadastro->cpf        = $input['cpf'];
        $cadastro->rg         = '' . $input['rg'] . '';
        $cadastro->filiacao   = $input['filiacao'];
        $cadastro->profissao  = $input['profissao'];
        $cadastro->empregador = $input['empregador'];
        // $cadastro->data_nascimento = ConverteData($input['data_nascimento']);
        $cadastro->sexo            = $input['sexo'];
        $cadastro->estado_civil    = $input['estado_civil'];
        $cadastro->tipo_uniao      = $input['tipo_uniao'];
        $cadastro->conjuge         = $input['conjuge'];
        $cadastro->c_cpf           = $input['c_cpf'];
        $cadastro->c_rg            = $input['c_rg'];

        $cadastro->razao_social    = $input['razao_social'];
        $cadastro->cnpj            = $input['cnpj'];
        $cadastro->insc_estadual   = $input['insc_estadual'];
        $cadastro->nome_fantasia   = $input['nome_fantasia'];
        $cadastro->faturamento     = $input['faturamento'];
        $cadastro->segmento        = $input['segmento'];
        $cadastro->socio           = $input['socio'];
        $cadastro->s_cpf           = $input['s_cpf'];
        $cadastro->s_rg            = $input['s_rg'];

        $cadastro->email           = $input['email'];
        $cadastro->telefone        = $input['telefone'];
        $cadastro->celular         = $input['celular'];

        $cadastro->cep             = $input['cep'];
        $cadastro->endereco        = $input['endereco'];
        $cadastro->numero          = $input['numero'];
        $cadastro->complemento     = $input['complemento'];
        $cadastro->bairro          = $input['bairro'];
        $cadastro->cidade          = $input['cidade'];
        $cadastro->estado          = $input['estado'];
        $cadastro->status          = $input['status'];
        $cadastro->desc_como_chegou = $input['desc_como_chegou'];
        

        $input['senha'] = trim($input['senha']);
        if ($input['senha'] != '') {
            $cadastro->changed_password = 0;
            $cadastro->senha = \Hash::make($input['senha']);
        }
        $cadastro->save();
        return redirect()->back();
    }

    /**
     * Delete the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function delete($id)
    {
        $id = (int) $id;
        $cadastro = Cadastros::findOrFail($id);
        $cadastro->delete();
        return redirect('cadastros');
    }

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function get($id)
    {
        $id = (int) $id;
        $cadastros = Cadastros::all();
        return view('admin.cads.index', ['cads' => $cadastros, 'vet' => Cadastros::findOrFail($id)->toArray()]);
    }

    public function documentos($id) {
        $docs = Cadastros::with('docPessoal', 'docEndereco')->findOrFail($id);
        $historico = HistoricoContato::where('idcadastro', $id)->orderBy('created_at', 'desc')->get();
        return view('admin.cads.verifica-documentos', ['vet' => $docs, 'idcadastro' => $id, 'historico' => $historico]);
    }

    public function salvaDocumento(Request $request) {
        
        if (!$request->arq_pessoal && !$request->arq_residencia) {
            return redirect()->back()->with(['error' => 'Você deve enviar ao menos um documento!']);
        }
        
        $idUsuario = UserSite::findOrFail((int) $request->codigo);
        $arrayExtension = ['pdf' =>'pdf', 'png' =>'png', 'doc' =>'doc', 'docx' =>'docx', 'jpeg' =>'jpeg', 'jpg' => 'jpg'];
        $arrayDoc = [];
        if ($request->arq_pessoal) {
            $arqName1 = $request->arq_pessoal->getClientOriginalName();
            $arqName1 = trim ($arqName1);
            $ext = $request->arq_pessoal->getClientOriginalExtension();
            $extVerify = strtolower(str_replace('.', '', $ext));
            if (!isset($arrayExtension[$extVerify])) {
                return redirect()->back()->with('error' , 'Você deve enviar um arquivo válido. Arquivos suportados: jpg, png, jpeg, pdf, doc e docx.');
            }
            $arqName1 = str_replace([' ', '/', '\\', '(', ')', '=', '+', '_'], '-', $arqName1);
            $arqName1 = str_replace('.'.$ext, '', $arqName1);
            $arqName1 = $arqName1 . '-' . md5($arqName1 . time() . $idUsuario->codigo) . '.' . $ext;
            $request->arq_pessoal->storeAs('documentos', $arqName1);
            $arrayDoc['arq_pessoal'] = $arqName1;
        }
        if ($request->arq_residencia) {
            $arqName2 = $request->arq_residencia->getClientOriginalName();
            $arqName2 = trim ($arqName2);
            $ext = $request->arq_residencia->getClientOriginalExtension();
            $extVerify = strtolower(str_replace('.', '', $ext));
            if (!isset($arrayExtension[$extVerify])) {
                return redirect()->back()->with('error' , 'Você deve enviar um arquivo válido. Arquivos suportados: jpg, png, jpeg, pdf, doc e docx.');
            }
            $arqName2 = str_replace([' ', '/', '\\', '(', ')', '=', '+', '_'], '-', $arqName2);
            $arqName2 = str_replace('.'.$ext, '', $arqName2);
            $arqName2 = $arqName2 . '-' . md5($arqName2 . time() . $idUsuario->codigo) . '.' . $ext;
            $request->arq_residencia->storeAs('documentos', $arqName2);
            $arrayDoc['arq_residencia'] = $arqName2;
        }
        if (count($arrayDoc) == 0) {
            return redirect()->back()->with('error' , 'Você deve enviar um arquivo válido.');
        }
        
        
        CadastrosDocumentos::updateOrCreate(['idcadastro' => $idUsuario->codigo],
                                                $arrayDoc
                                            );
                                            
                                            
        $user = Auth::guard('admin')->user();      
        if ($user) {
            $nomeUsuario = $idUsuario->pessoa == 1 ? $idUsuario->nome : $idUsuario->razao_social;
            $historico = new HistoricoContato();
            $historico->usuario = '[#' . $user->codigo . '] ' . $user->nome;
            $historico->historico = 'Enviou documentos para o usuario ' . $nomeUsuario;
            $historico->idcadastro = $idUsuario->codigo;
            $historico->save();
        }                     
        // $mensagem = "O usuário " . $user->nome . " enviou documentos pessoais do cliente<br>";
        // $mensagem .= " Email de destino: <strong>" . $cadastro->email . "</strong><br>";
        // $mensagem .= " Data: <strong>" . date('d/m/Y H:i:s') . "</strong><br>";
        // $mensagem .= "<hr>";
        // $mensagem .= $request->mensagem;
        // $mensagem .= "<hr>";
        return redirect()->back()->with('success' , 'Arquivo enviado com sucesso!');
    }

    public function aprovarSemDocumento($tipo, $codigo) {
        $user = Auth::guard('admin')->user();
        $cadastro = Cadastros::findOrFail($codigo);
        $documento1 = CadastrosDocumentos::where('idcadastro', $codigo)->first();
        $hasDoc = true;
        if ($documento1 == null) {
            $documento1 = new CadastrosDocumentos;
            $hasDoc = false;
        }
        $documento1->idcadastro = $codigo;
        if ($tipo == 'residencia') {
            $documento1->status_residencia = 1;
            $documento1->save(); 
            if ($user) {
                $historico = new HistoricoContato();
                $historico->usuario = '[#' . $user->codigo . '] ' . $user->nome;
                $historico->historico = 'Aprovou comprovante de residência';
                $historico->idcadastro = $cadastro->codigo;
                $historico->save();
            }
            if ($hasDoc) {
                $cadastro->status = 1;
                $cadastro->save();
                Mail::to($cadastro->email)
                ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-todos-documentos', 'Acesso liberado'));
                return redirect('/cadastros/documento/' . $codigo)->with('success', 'Operação concluída');
            }
            Mail::to($cadastro->email)
            ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-documento-residencia', 'Comprovante de residência aprovado'));
            
            return redirect('/cadastros/documento/' . $codigo)->with('success', 'Operação concluída');  
        }
        if ($tipo == 'pessoal') {
            $documento1->status_pessoal = 1;
            $documento1->save();
            if ($user) {
                $historico = new HistoricoContato();
                $historico->usuario = '[#' . $user->codigo . '] ' . $user->nome;
                $historico->historico = 'Aprovou documento pessoal.';
                $historico->idcadastro = $cadastro->codigo;
                $historico->save();
            }
            if ($hasDoc) {
                $cadastro->status = 1;
                $cadastro->save();
                Mail::to($cadastro->email)
                ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-todos-documentos', 'Acesso liberado'));
                return redirect('/cadastros/documento/' . $codigo)->with('success', 'Operação concluída');
            }

            Mail::to($cadastro->email)
            ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-documento-pessoal', 'Documento pessoal aprovado'));
            return redirect('/cadastros/documento/' . $codigo)->with('success', 'Operação concluída');
        }
    }
    
    public function aprovaDocumento($codigo, $tipo) {
        $user = Auth::guard('admin')->user();
        $documento = CadastrosDocumentos::findOrFail($codigo);
        $tipo = (int) $tipo;
        if ($tipo != 1 && $tipo !=2) {
            return redirect()->back();
        }
        $aprovaTodos = false;
        $aprovaPessoal = false;
        $aprovaResidencia = false;
        if ($tipo == 1) {
            $documento->status_pessoal = 1;
            $aprovaPessoal = true;
        }

        if ($tipo == 2) {
            $documento->status_residencia  = 1;
            $aprovaResidencia = true;
        }

        if ($aprovaPessoal == true && $aprovaResidencia == false) {
            $documento->save();
            $cadastro = Cadastros::with('docPessoal', 'docEndereco')->find($documento->idcadastro);
            if ($user) {
                $historico = new HistoricoContato();
                $historico->usuario = '[#' . $user->codigo . '] ' . $user->nome;
                $historico->historico = 'Aprovou documento pessoal.';
                $historico->idcadastro = $cadastro->codigo;
                $historico->save();
            }
            if ($cadastro->docPessoal->status_pessoal  == 1 && $cadastro->docEndereco->status_residencia == 1) {
                Mail::to($cadastro->email)
                ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-todos-documentos', 'Acesso liberado'));
                $cadastro->status = 1;
                $cadastro->save();
                return redirect('/cadastros/documento/' . $cadastro->codigo)->with('success', 'Operação concluída');
            }
            Mail::to($cadastro->email)
            ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-documento-pessoal', 'Documento pessoal aprovado'));
            return redirect('/cadastros/documento/' . $cadastro->codigo)->with('success', 'Operação concluída');
        }

        if ($aprovaPessoal == false && $aprovaResidencia == true) {
            $documento->save();
            $cadastro = Cadastros::with('docPessoal', 'docEndereco')->find($documento->idcadastro);
            if ($user) {
                $historico = new HistoricoContato();
                $historico->usuario = '[#' . $user->codigo . '] ' . $user->nome;
                $historico->historico = 'Aprovou comprovante de residência.';
                $historico->idcadastro = $cadastro->codigo;
                $historico->save();
            }
            if ($cadastro->docPessoal->status_pessoal  == 1 && $cadastro->docEndereco->status_residencia == 1) {
                Mail::to($cadastro->email)
                ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-todos-documentos', 'Acesso liberado'));
                $cadastro->status = 1;
                $cadastro->save();
                return redirect('/cadastros/documento/' . $cadastro->codigo)->with('success', 'Operação concluída');
            }
            Mail::to($cadastro->email)
            ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-documento-residencia', 'Comprovante de residência aprovado'));
            return redirect('/cadastros/documento/' . $cadastro->codigo)->with('success', 'Operação concluída');
        }
        if ($aprovaTodos == true) {
            Mail::to($cadastro->email)
            ->queue(new AprovaDocumentos($cadastro, 'layout.email.aprova-todos-documentos'));
            return redirect('/cadastros/documento/' . $cadastro->codigo)->with('success', 'Operação concluída');
        }
        return redirect('/cadastros/documento/' . $cadastro->codigo);
    }
    
    public function enviaEmailParaUsuario(Request $request) {
        $cadastro = Cadastros::findOrFail($request->codigo);
        $mensagem = $request->mensagem;
        $mensagem = trim($mensagem);
        if ($mensagem == '') {
            return redirect('/cadastros/documento/' . $request->codigo)->with('error', 'O corpo da mensagem não pode ser vazio.');    
        }
        $user = Auth::guard('admin')->user();
        Mail::to($cadastro->email)
            ->queue(new EmailGenerico('Observação sobre documentos enviados', $mensagem));
        if ((int) $user->tipo != 1) {
            $mensagem = "O usuário " . $user->nome . " enviou o email abaixo para um cliente. Segue a mensagem:<br>";
            $mensagem .= " Email de destino: <strong>" . $cadastro->email . "</strong><br>";
            $mensagem .= " Data: <strong>" . date('d/m/Y H:i:s') . "</strong><br>";
            $mensagem .= "<hr>";
            $mensagem .= $request->mensagem;
            $mensagem .= "<hr>";
        }
        if ($user) {
            $historico = new HistoricoContato();
            $historico->usuario = '[#' . $user->codigo . '] ' . $user->nome;
            $historico->historico = 'Enviou mensagem.<hr>' . $mensagem;
            $historico->idcadastro = $cadastro->codigo;
            $historico->save();
        }
        return redirect('/cadastros/documento/' . $request->codigo)->with('success', 'O email foi enviado com sucesso.');

    }
}