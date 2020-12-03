<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Lotes;
use App\Leiloes;
use App\LotesImagens;
use App\LotesAnexos;
use App\Estados;

class LotesController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        // $lotes = Lotes::findOrFail($id);
        // echo $lotes->idestado;die;
        return view('admin.lotes.index', ['user' => Lotes::findOrFail($id)]);
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
        
        return view('admin.lotes.index', ['cads' => $vet]);
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
        $cadastros = new Lotes();
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

    public function getLotes($id)
    {
        $lotes = Lotes::with('leilao')->where('idleilao', '=', (int) $id)->get();
        $leilaoData = $lotes->count() > 0 ? $lotes[0]->leilao : Leiloes::findOrFail($id);
        $leilao = stripslashes(@$lotes[0]->titulo);
        return view('admin.lotes.index', ['lotes' => $lotes, 'cidades' => [], 'leilaoData' => $leilaoData, 'idleilao' => @$lotes[0]->idleilao ?? $id, 'leilao' => $leilao]);
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
        
        $cadastros = new Lotes();
        $input['codigo'] = (int) $input['codigo'];
        
        if ($input['codigo'] > 0) {
            $cadastros = Lotes::find($input['codigo']);
        }
        $leilao = Leiloes::find($input['idleilao']);
        $cadastros->idleilao = $input['idleilao'];
        $cadastros->categoria = $input['categoria'];
        
        $cadastros->subcategoria = $input['subcategoria'];
        $cadastros->idestado = $input['idestado'];
        $input['idcidade'] = explode("|", $input['idcidade']);
        $cadastros->idcidade = $input['idcidade'][1];
        $cadastros->bairro = $input['bairro'];
        $cadastros->endereco = $input['endereco'];
        $cadastros->numero = $input['numero'];
        $cadastros->titulo = $input['titulo'];
        $cadastros->subtitulo = $input['subtitulo'];
        $input['avaliacao'] = trim($input['avaliacao']) == '' ? 0 : $input['avaliacao'];
        $cadastros->avaliacao = str_replace(",", ".", str_replace(".", "", $input['avaliacao']));
        $input['min_venda'] = trim($input['min_venda']) == '' ? 0 : $input['min_venda'];
        $cadastros->min_venda = str_replace(",", ".", str_replace(".", "", $input['min_venda']));
        $input['debitos'] = trim($input['debitos']) == '' ? 0 : $input['debitos'];
        $cadastros->debitos = str_replace(",", ".", str_replace(".", "", $input['debitos']));
        
        $cadastros->lance_data_1 = str_replace(",", ".", str_replace(".", "", $input['lance_data_1']));;
        
        $cadastros->lance_data_2 = str_replace(",", ".", str_replace(".", "", $input['lance_data_2']));;
        $cadastros->incremento = str_replace(",", ".", str_replace(".", "", $input['incremento']));
        
        $timeStampAbertura = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $input['abertura'])->timestamp;
        
        $cadastros->abertura = $input['abertura'];
        $cadastros->fechamento = $input['fechamento'];
        $cadastros->num_processo = $input['num_processo'];
        $cadastros->url_consulta = $input['url_consulta'];
        $cadastros->vara = '';
        $cadastros->juiz = '';
        $cadastros->comissao_leiloeiro = str_replace(",", ".", str_replace(".", "", $input['comissao_leiloeiro']));
        $cadastros->nome_exequente = '';
        $cadastros->doc_exequente = '';
        $cadastros->nome_executado = '';
        $cadastros->doc_executado = '';
        $cadastros->nome_depositario = '';
        $cadastros->cpf_depositario = '';
        $cadastros->rg_depositario = '';
        $cadastros->edital = '';
        $cadastros->publicacao = $input['publicacao'];
        $cadastros->doe = $input['doe'];
        $cadastros->cda = $input['cda'];
        $cadastros->chb = $input['chb'];
        $cadastros->descricao = $input['descricao'];
        $cadastros->visitacao = $input['visitacao'];
        $cadastros->compra_parcelada = $input['compra_parcelada'];
        $cadastros->mapa = $input['mapa'];
        $cadastros->lote_destaque = $input['lote_destaque'];
        $cadastros->requer_habilitacao = 2;//$input['requer_habilitacao'];
        $cadastros->exibir_valor = 2;//$input['exibir_valor'];
        
        $cadastros->encerrado = $input['encerrado'];//$leilao->tipo == '4' ? 1 : $leilao->encerrado;
        $cadastros->restrito = 2;//$input['restrito'];
        $cadastros->suspender = 2;
        $cadastros->ind_parcelada = $input['ind_parcelada'];
        
        $cadastros->ind_status = 3;

        $cadastros->save();
        
        $ordem = 1;
        
        if(isset($request->imagens)) {
            foreach($request->imagens as $imagem) {
                $img = new LotesImagens;
                $nome = md5(uniqid() . $imagem->getClientOriginalName());
                $ext = $imagem->getClientOriginalExtension();
                $nome = $nome . '.' .$ext;
                $imagem->storeAs('public/imagens', $nome);
                $img->idlote = $cadastros->codigo;
                $img->arquivo = $nome;
                $img->ordem = $ordem; 
                $img->save();
                $ordem++;
            }
        }
        return redirect()->back()->with('success', 'Operação concluída.');
    }

    /**
     * Delete the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function delete($idleilao, $id)
    {
        $id = (int) $id;
        $cadastro = Lotes::find($id);
        $cadastro->delete();
        return redirect('lotes/leilao/' . $idleilao);
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
        $lote = Lotes::with('imagensLote')->findOrFail($id)->toArray();
        
        $lotes = Lotes::where('idleilao', '=', (int) $lote['idleilao'])->get();
        $cidades = Estados::with('cidades')->find($lote['idestado']);
        $leilao = stripslashes(@$lotes['titulo']);
        return view('admin.lotes.index', ['vet' => $lote, 'lotes' => $lotes, 
                                         'idleilao' => @$lotes[0]->idleilao, 
                                         'cidades' => $cidades->cidades,
                                         'idcidade' => $lote['idcidade'],
                                         'idcategoria' => $lote['categoria'],
                                         'idsubcategoria' => $lote['subcategoria'],
                                         'leilao' => $leilao]);
    }

    /**
     * Exclui a imagem do lote
     */
    public function excluirImagem($id)
    {
        $imagem = LotesImagens::findOrFail($id);
        $idLote = $imagem->idlote;
        $pathImagem = storage_path() . '/app/public/imagens/' . $imagem->arquivo;
        if (file_exists($pathImagem)) {
            unlink($pathImagem);
        }
        $imagem->delete();
        return redirect('/lotes/' . $idLote )->with('success', 'Operação concluída.');
    }

    public function getImages($id)
    {
        $lote = Lotes::with('leilao')->find($id);
        $imagens = $lote->imagensLote;
        return view('admin.lotes.ordenar-imagens-lote', ['lote' => $lote, 'imagens' => $imagens]);
    }

    public function getAnexos($id)
    {
        $lote = Lotes::find($id);
        $anexos = $lote->anexosLote;
        return view('admin.lotes.lotes-anexos', ['lote' => $lote, 'anexos' => $anexos]);
    }

    public function editarAnexo($idLote, $codigo)
    {
        $lote = Lotes::find($idLote);
        $anexos = $lote->anexosLote;
        $anexo = LotesAnexos::findOrFail($codigo);
        return view('admin.lotes.lotes-anexos', ['lote' => $lote, 'anexos' => $anexos, 'anexo' => $anexo]);
    }

    public function updateOrderImage(Request $request)
    {
        $input = $request->all();
        $updateRecordsArray = $input['recordsArray'];
        if (count($updateRecordsArray) > 0) {
            $ind = 1;
            foreach ($updateRecordsArray as $recordIDValue)
            {
                $codigo = $recordIDValue;
                $imagem = LotesImagens::findOrFail($codigo);
                
                $imagem->ordem = $ind;
                $imagem->save();
                $ind = $ind + 1;	
            }
            if($ind == (count($updateRecordsArray) + 1))
            {
                return 'Imagens ordenadas com sucesso!';
            }
        }
        return 'error';
    }

    public function removeAnexo($codigo)
    {
        $anexo = LotesAnexos::findOrFail($codigo);
        $arquivo = $anexo->arquivo;
        $anexo->delete();
        Storage::delete('public/documentos/' . $arquivo);
        return redirect('/lote/' . $anexo->idlote . '/anexos');
    }

    public function salvaAnexo(Request $request)
    {
        $input = $request->all();
        $originalName = '';
        $anexos = new LotesAnexos;
        $codigo = (int) $request->codigo;
        if ($codigo > 0) {
            $anexos = LotesAnexos::find($codigo);
        }
        if ($request->anexos) {
            $originalName = $request->anexos->getClientOriginalName();
            $ext = $request->anexos->getClientOriginalExtension();
            $originalName = str_replace([' ', '/', '\\', '(', ')', '=', '+', '_'], '-', $originalName);
            $originalName = str_replace('.'.$ext, '', $originalName);
            $originalName = $originalName . '-' . md5($originalName.time().$codigo) . '.' . $ext;
            $request->anexos->storeAs('public/documentos', $originalName);
        }
        $nome = trim ($request->nome);
        $nome  = $nome  == '' ? $originalName : $nome;//md5($nome . $codigo);
        $anexos->idlote = (int) $request->idlote;
        if ($originalName != '') {
            $anexos->arquivo = $originalName;
        }
        $anexos->nome = $nome;
        $anexos->save();
        
        return redirect('/lote/' . $anexos->idlote . '/anexos');
    }

    public function getSubcat(Request $request)
    {
        $id = $request->idcategoria;
        
        return view('admin.partials.combo-subcat', ['idcategoria' => $id]);
    }
}