<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Leiloes;

class LeiloesController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        return view('admin.leiloes.index', ['user' => User::findOrFail($id)]);
    }
    /**
     * List all profiles.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function index()
    {
        $vet = Leiloes::all();
        
        return view('admin.leiloes.index', ['cads' => $vet]);
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
       
        $cadastro = new Leiloes();
        $input['codigo'] = (int) $input['codigo'];
        $cadastro->data_cadastro = date('Y-m-d') ;
        
        if ($input['codigo'] > 0) {
            $cadastro = Leiloes::find($input['codigo']);
            unset($cadastro->data_cadastro);
        }
        $cadastro->tipo     = $input['tipo'];
        $input['modalidade'] = trim($input['modalidade']);
        $cadastro->modalidade   = $input['modalidade'] == '' ? 1 : $input['modalidade'];
        $cadastro->modelo       = 1;//$input['modelo'];
        $cadastro->idleiloeiro  = 3;//(int) $input['idleiloeiro'];
        $cadastro->habilitacao   = 1;//(int) $input['habilitacao'];
        $cadastro->titulo  = $input['titulo'];
        $cadastro->subtitulo = $input['subtitulo'];
        $cadastro->suspender = (int) $input['suspender'];
        
        $cadastro->numero            = '';
        $cadastro->publicacao    = @$input['publicacao'];
        $cadastro->idcomitente      = 0;//$input['idcomitente'];
        $cadastro->leilao_data_tipo      = $input['leilao_data_tipo'];
        $timeStampDatainicial = \Carbon\Carbon::createFromFormat('d/m/Y', $input['leilao_data_inicial'])->timestamp;
        
        $timeStampDatafinal = \Carbon\Carbon::createFromFormat('d/m/Y', $input['leilao_data_final'])->timestamp;
        $timeStampDatainicial2 = \Carbon\Carbon::createFromFormat('d/m/Y', $input['leilao2_data_inicial'])->timestamp;
        $timeStampDatafinal2 = \Carbon\Carbon::createFromFormat('d/m/Y', $input['leilao2_data_final'])->timestamp;
         
        $cadastro->leilao_data_inicial   = date('Y-m-d', $timeStampDatainicial);
        $cadastro->leilao_hora_inicial   = $input['leilao_hora_inicial'];

        $cadastro->leilao_data_final    = date('Y-m-d', $timeStampDatafinal);
        $cadastro->leilao_hora_final    = $input['leilao_hora_final'];
        $cadastro->leilao2_data_tipo   = $input['leilao2_data_tipo'];
        $cadastro->leilao2_data_inicial   = date('Y-m-d', $timeStampDatainicial2);
        $cadastro->leilao2_hora_inicial   = $input['leilao2_hora_inicial'];
        $cadastro->leilao2_data_final     = date('Y-m-d', $timeStampDatafinal2);
        $cadastro->leilao2_hora_final     = $input['leilao2_hora_final'];
        $cadastro->responsavel           = '';//$input['responsavel'];
        $cadastro->endereco            = '';

        $cadastro->condicao            = trim($input['condicao']);
        $cadastro->desconto            = trim($input['desconto']);

        $cadastro->cidade           = '';
        $cadastro->visitacao        = '';//$input['visitacao'];
        $cadastro->restrito         = 2;//$input['restrito'];
        $cadastro->usar_cronometro       = (int) @$input['cronometro'];
        
        if ($request->jornal) {
            $cadastro->jornal       = md5($request->jornal->getClientOriginalName() . time() . $cadastro->titulo . $input['codigo']);
            $request->jornal->storeAs('public/documentos', $cadastro->jornal);
        }
        if ($request->edital) {
            $edital = $request->edital->getClientOriginalName();
            $ext = $request->edital->getClientOriginalExtension();
            $edital = str_replace([' ', '/', '\\', '(', ')', '=', '+', '_'], '-', $edital);
            $edital = str_replace('.'.$ext, '', $edital);
            $edital = $edital . '-' . md5($edital.time().$input['codigo']) . '.' . $ext;

            $cadastro->edital    = $edital;
            $request->edital->storeAs('public/documentos', $cadastro->edital);
        }
        if ($request->logo) {
            $cadastro->logo        = $request->logo->getClientOriginalName();
            $request->logo->storeAs('public/imagens', $cadastro->logo);
        }
        if ($request->destaque) {
            $imagem  = str_replace(" ", "-", $request->destaque->getClientOriginalName());
            $ext = explode('.', $imagem);
            $ext = $ext[count($ext) - 1];
            $imagem =  md5($imagem . $input['titulo']) . '.' . $ext;
            $cadastro->destaque     = $imagem;
            $request->destaque->storeAs('public/imagens', $cadastro->destaque);
            $request->destaque->storeAs('public/imagens/thumb', $cadastro->destaque);
            $imgThumb = storage_path('app/public') . '/imagens/thumb/'.$cadastro->destaque;
            
            redimencionaImagem($imgThumb, 250, 166);
        }
        $cadastro->imagem_360 = '';
        if ($request->imagem_360) {
            $cadastro->imagem_360   = $request->imagem_360->getClientOriginalName();
            $request->imagem_360->storeAs('public/imagens', $cadastro->imagem_360);
        }
        $cadastro->youtube      = $input['youtube'];
        $cadastro->condicoes    = $input['condicoes'];
        $cadastro->regras       = $input['regras'];
        $cadastro->desconto_p   = (int) $input['desconto_p'];
        
        //status futuro
        $timeStampDatainicial = strtotime($cadastro->leilao_data_inicial . ' ' . $cadastro->leilao_hora_inicial);
        
        $cadastro->encerrado    = (int) $input['encerrado'];

        $cadastro->save();

        $loteAbertura = explode('-', $cadastro->leilao_data_inicial);
        $loteAbertura = explode('-', $cadastro->leilao_data_inicial);
        $loteAbertura = $loteAbertura[2] . '/' . $loteAbertura[1] . '/' . $loteAbertura[0] . ' ' . substr($cadastro->leilao_hora_inicial, 0, 5);
        
        $loteFechamento = explode('-', $cadastro->leilao2_data_final);
        $loteFechamento = explode('-', $cadastro->leilao2_data_final);
        $loteFechamento = $loteFechamento[2] . '/' . $loteFechamento[1] . '/' . $loteFechamento[0] . ' ' . substr($cadastro->leilao2_hora_final, 0, 5);

        \App\Lotes::where('idleilao','=', $cadastro->codigo)
                   ->update([
                       'encerrado' => $cadastro->encerrado, 
                       'abertura' => $loteAbertura, 
                       'fechamento' => $loteFechamento]);
        
        return redirect()->back()->with('success', 'Operação concluída.');
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
        $cadastro = Leiloes::find($id);
        $cadastro->delete();
        return redirect('/cadastro-leiloes');
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
        $cadastros = Leiloes::all();
        return view('admin.leiloes.index', ['cads' => $cadastros, 'vet' => Leiloes::findOrFail($id)->toArray()]);
    }

    public function exportaImagem() {
        $dir = scandir('/home/mario/workspace/novo-alfaleiloes/public/storage/imagens');    
        $path = '/home/mario/workspace/novo-alfaleiloes/public/storage/imagens/thumb/';
        foreach($dir as $imagem) {
            $nomeImagem = strtolower($imagem);
            if (strstr($nomeImagem, 'png') || strstr($nomeImagem, 'jpeg') || strstr($nomeImagem, 'jpg') || strstr($nomeImagem, 'jpeg')) {
                $imgThumb = $path . $imagem;

                redimencionaImagem($imgThumb, 166, 100);
            }
            continue;
        }
        die;
        $cadastro->destaque     = $request->destaque->getClientOriginalName();
            $request->destaque->storeAs('public/imagens', $cadastro->destaque);
            $request->destaque->storeAs('public/imagens/thumb', $cadastro->destaque);
            $imgThumb = storage_path('app/public') . '/imagens/thumb/'.$cadastro->destaque;
            
            redimencionaImagem($imgThumb, 166, 100);
    }
}