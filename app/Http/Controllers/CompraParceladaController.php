<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Lotes;
use Auth;
use App\CadastrosHabilitados;
use App\LancesParcelados;
use App\ComprasParceladas;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmaLance;
use App\Routines\LanceRoutine;

class CompraParceladaController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function verLote($id)
    {
        $user = Auth::user();
        $lote = Lotes::with('imagensLote', 'leilao')->findOrFail($id);
        if ($lote->ind_parcelada != 1) {
            return redirect()->back()->with('success', 'Não é possível ofertar proposta de compra neste leilão!');
        }
        // if ($lote->encerrado == 7) {
        //     return redirect()->back()->with('success', 'Este leilão não ainda não está aberto. Não é possível realizar proposta de compra.');
        // }
        if ($lote->encerrado != 1 && $lote->encerrado != 7) {
            return redirect()->back()->with('success', 'Este leilão está encerrado. Não é possível realizar proposta de compra.');
        }
        if (!$user) {
            return redirect()->back()->with('success', 'Você precisa estar logado e habilitado para ofertar proposta!');
        }
        
        return view('site.leiloes.compra-parcelada', ['lote' => $lote, 'user' => $user]);
    }

    public function ofertar(Request $request, $id) 
    {
        $user = Auth::user();
        
        $lote = Lotes::with('leilao')->findOrFail($id);
        if ($lote->encerrado != 1 && $lote->encerrado != 7) {
            return redirect()->back()->with('success', 'Não é possível ofertar proposta de compra neste leilão!');
        }
        $time = time();
        
        $formatDate = strlen($lote->fechamento) < 19 ? 'd/m/Y H:i' : 'd/m/Y H:i:s';

        $fechamento = \Carbon\Carbon::createFromFormat($formatDate, $lote->fechamento)->timestamp;
        
        if ($time > $fechamento) {
            return redirect()->back()->with('success', $time. "=>$fechamento=>". date('Y-m-m H:i:s',  $time) ."=>". date('Y-m-m H:i:s', $fechamento));
        }
        if (!$user) {
            return redirect()->back()->with('success', 'Você precisa estar logado e habilitado para ofertar proposta!');
        }

        if (CadastrosHabilitados::where('idcadastro', $user->codigo)
                                 ->where('idlote', $id)
                                 ->get()->count() == 0) {
            return redirect()->back()->with('success', 'Você precisa estar logado e habilitado para ofertar proposta!');
        }

        if ($lote->ind_parcelada != 1) {
            return redirect()->back()->with('success', 'Não é possível ofertar proposta de compra neste leilão!');
        }

        $valor_minimo = $request->valor_minimo;
        $valor_oferta = $request->valor_oferta;
        $valor_oferta_lance = $request->valor_oferta_lance;
        $valor_entrada = $request->valor_entrada;
        $valor_entrada_lance = $request->valor_entrada_lance;
        $valor_entrada_lance_h = $request->valor_entrada_hidden;
        $parcelas = $request->parcelas;
        $observacoes = $request->observacoes;

        $oferta = str_replace(",", ".", str_replace(".", "", $valor_minimo));
        if($valor_oferta == 'selecionar') {
            $oferta = str_replace(",", ".", str_replace(".", "", $valor_oferta_lance));
        }

        $entrada = round(str_replace(",", ".", str_replace(".", "", $valor_minimo)) * 0.25, 2);
        if($valor_entrada == 'selecionar') {
            $entrada = str_replace(",", ".", str_replace(".", "", $valor_entrada_lance));
        }

        if($valor_entrada == 'minimo') {
            $entrada = str_replace(",", ".", str_replace(".", "", $valor_entrada_lance_h));
        }
       
        $v_minimo = str_replace(",", ".", str_replace(".", "", $valor_minimo));
        $v_minimo = (float) $v_minimo;
        $vMin50Percent = $v_minimo - ($v_minimo * 0.50);
        
        $oferta = (float) $oferta;
        if ($oferta < $vMin50Percent) {
            return redirect()->back()->with('success', 'O valor mínimo para esta oportunidade precisa ser de, pelo menos, R$ ' . number_format($vMin50Percent, 2, ",", ".") );
        }
        $minimo_entrada = $v_minimo * 0.25;
        if($entrada < $minimo_entrada) {
            return redirect()->back()->with('success', 'O valor informado para entrada precisa ser maior do que 25% do valor do mínimo lance!');
        }

        $dataInicial =  strtotime($lote->leilao->leilao_data_inicial . ' ' . $lote->leilao->leilao_hora_inicial);
        $dataFinal =  strtotime($lote->leilao->leilao2_data_final . ' ' . $lote->leilao->leilao2_hora_final);

        $lance_minimo = $lote->lance_data_2;
        
        if($time <= $dataInicial) {
            $lance_minimo = $lote->lance_data_1;
        }
        $dataLance = date('Y-m-d H:i:s');
        $lance = new LancesParcelados();
        $lance->idcadastro = $user->codigo;
        $lance->idlote = $id;
        $lance->desativado  = 0;
        $lance->data_lance = $dataLance;
        $lance->data_cronometro = $dataLance;
        $lance->ind_cronometro = 0;
        $lance->valor = $oferta;
        $lance->save();

        $lanceParcelado = new ComprasParceladas();
        $lanceParcelado->idcadastro = $user->codigo;
        $lanceParcelado->idlote = $id;
        $lanceParcelado->idlance = $lance->codigo;
        $lanceParcelado->oferta = $oferta;
        $lanceParcelado->entrada = $entrada;
        $lanceParcelado->parcelas = $parcelas;
        $lanceParcelado->observacoes = $observacoes;
        $lanceParcelado->data = $dataLance;
        $lanceParcelado->save();


        //executa a função de adição de minutos
        $rotinaLance = new LanceRoutine;
        $rotinaLance->addMinutes($lote);

        Mail::to($user->email)
        ->queue(new ConfirmaLance($user, 'layout.email.confirma-compra-parcelada', 'Confirmamos sua compra parcelada!', 
                                  $oferta, 
                                  $lote->leilao->titulo, 
                                  $lote->leilao->tipo));
        Mail::cc(getAllEmails())
        ->queue(new ConfirmaLance($user, 'layout.email.confirma-lance-adm', 'Nova proposta de compra enviada!', $oferta, $lote->leilao['titulo']));
        
        $url = '/leilao/lote/'.$id.'/' . urlTitulo($lote->leilao->titulo);
        return redirect($url)->with('success', 'Proposta de compra enviada com sucesso!');
    }
}