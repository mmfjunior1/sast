<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Lotes;
use App\Leiloes;
use App\Lances;
use App\LanceAutomatico;
use App\LancesParcelados;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmaLance;
use App\Routines\LanceRoutine;
use Auth;

class LancesController extends Controller
{
    public function verLances($idlote)
    {
        $lote = Lotes::findOrFail($idlote);
        $titulo = $lote->titulo;

        // maior lance (acho que isso nao está certo, mas a rotina antiga é exatamente essa. Devemos verificar)
        $lancesParc = Db::table('lances_parcelados as A')
                ->select("A.codigo AS id", "A.valor", "A.data_lance as dt_lance", "B.pessoa", 
                "B.nome", "B.razao_social", DB::raw("'2' AS tipo"))
                ->distinct()
                ->join('cadastros as B','A.idcadastro', 'B.codigo' )
                ->where('idlote', $idlote)
                ->where('desativado', '!=', 1);
        $maiorLance = Db::table('lances as A')
                ->select("A.codigo AS id", "A.valor", "A.data_lance as dt_lance", "B.pessoa", 
                "B.nome", "B.razao_social", DB::raw("'1' AS tipo"))
                ->distinct()
                ->join('cadastros as B','A.idcadastro', 'B.codigo' )
                ->where('idlote', $idlote)
                ->where('desativado', '!=', 1)
                ->union($lancesParc)->orderBy('valor', 'desc')->limit(1)->get();
        
        $idlance = @$maiorLance[0]->id;
        // lances superados
        
        $lancesParc1 = Db::table('lances_parcelados as A')
                ->select("A.codigo AS id", "A.valor", "A.data_lance as dt_lance", "B.pessoa", 
                "B.nome", "B.razao_social", DB::raw("'2' AS tipo"))
                ->distinct()
                ->join('cadastros as B','A.idcadastro', 'B.codigo' )
                ->where('idlote', $idlote)
                ->where('desativado', '!=', 1)
                ->where('A.codigo', '!=', $idlance);
        $lanceSuperado = Db::table('lances as A')
                ->select("A.codigo AS id", "A.valor", "A.data_lance as dt_lance", "B.pessoa", 
                "B.nome", "B.razao_social", DB::raw("'1' AS tipo"))
                ->distinct()
                ->join('cadastros as B','A.idcadastro', 'B.codigo' )
                ->where('idlote', $idlote)
                ->where('A.codigo', '!=', $idlance)
                ->where('desativado', '!=', 1)
                ->union($lancesParc1)->orderBy('id', 'desc')->get();

        $lancesParcTot = Db::table('lances_parcelados as A')
                ->select("A.codigo AS id", "A.valor","A.data_lance as dt_lance", "B.pessoa", 
                "B.nome", "B.cpf", "B.razao_social", DB::raw("'2' AS tipo"), 'compras_parceladas.*', 'lotes.*')
                ->distinct()
                ->join('cadastros as B','A.idcadastro', 'B.codigo' )
                ->join('compras_parceladas','A.codigo', 'compras_parceladas.idlance' )
                ->join('lotes','A.idlote', 'lotes.codigo' )
                ->where('A.idlote', $idlote)
                ->get();
        $lancesParcTot = $lancesParcTot->toArray();

        $lancesCancelados = Db::table('lances as A')
        ->select("A.codigo AS id", "A.valor","A.usuario_desativa", "A.data_desativa", "A.data_lance as dt_lance", "B.pessoa", 
        "B.nome", "B.razao_social", DB::raw("'1' AS tipo"))
        ->distinct()
        ->join('cadastros as B','A.idcadastro', 'B.codigo' )
        ->where('idlote', $idlote)
        ->where('A.codigo', '!=', $idlance)
        ->where('desativado', '=', 1);
        $lancesParcCanc = Db::table('lances_parcelados as A')
                ->select("A.codigo AS id", "A.valor","A.usuario_desativa", "A.data_desativa", 
                "A.data_lance as dt_lance", "B.pessoa", 
                "B.nome", "B.razao_social", DB::raw("'2' AS tipo"))
                ->distinct()
                ->join('cadastros as B','A.idcadastro', 'B.codigo' )
                ->where('idlote', $idlote)
                ->where('desativado', '=', 1)
                ->union($lancesCancelados)->get();

        return view('admin.lances.lances', ['maiorLance' => $maiorLance, 
                                           'lancesSuperados' => $lanceSuperado,
                                           'lancesParcTot' => $lancesParcTot,
                                           'lancesCancelados' => $lancesParcCanc,
                                           'titulo' => $titulo, 
                                           'idlote' => $idlote]);
        
    }
    /**
     * Exclui um lance
     */
    public function excluirLance($id) {
        $lance = Lances::findOrFail($id);
        $lance->delete();
        return redirect()->back();
    }

    public function desativarLance(Request $request) {
        $user = Auth::guard('admin')->user();
        $id = (int) $request->id;
        $class = Lances::class;
        if ($request->tipo == '2') {
            $class = LancesParcelados::class;
        }
        $lance = $class::findOrFail($id);
        $lote = Lotes::findOrFail($lance->idlote);
        if ($lote->encerrado == 8) {
            return response()->json(['status' => true, 'msg' => 'Este leilão está encerrado. Não é possível excluir o lance.']);    
        }
        $lance->obs = $request->obs;
        $lance->desativado = 1;
        $lance->data_desativa = date('Y-m-d H:i:s');
        $lance->usuario_desativa = $user->nome;
        $lance->save();
        return response()->json(['status' => true, 'msg' => 'ok']);
    }

    /**
     * Exclui um lance parcelado
     */
    public function excluirLanceParc($id) {
        $lance = LancesParcelados::findOrFail($id);
        $lance->delete();
        return redirect()->back();
    }
    /**
     * Declara um lance parcelado como vencedor
     */
    public function declararVencedor($id)
    {
        $lance = LancesParcelados::findOrFail($id);
        //atualiza tudo para 0
        LancesParcelados::where('idlote', $lance->idlote)->update(['status' => 0]);
        // segue marcando o último como escolhido
        $lance->status = '1';
        $lance->save();
        $lote = Lotes::findOrFail($lance->idlote);
        $lote->encerrado = 8;
        $lote->save();
        $leilao = Leiloes::findOrFail($lote->idleilao);
        $leilao->encerrado = 8;
        $leilao->save();
        return redirect('/lances/lote/' . $lance->idlote)->with(['success' => 'Arrematante selecionado com sucesso.' ]);
    }

    public function addLance(Request $request, $codigo) {
        $lote = Lotes::with('leilao', 'maiorLance')->findOrFail(1);
        
        $user = Auth::user();
        $lanceRoutine = new LanceRoutine();
        
        $leilao = $request->get('leilao');
        $lote = $request->get('lote');

        $leilaoInfo = new \StdClass();
        
        $leilaoInfo->leilao = $request->get('leilao');
        $leilaoInfo->lote = $request->get('lote');
        $leilaoInfo->valorLance = $request->get('valorLance');

        $lanceRoutine->addLance($leilaoInfo, $codigo, $user->codigo);
        
        Mail::to($user->email)
        ->queue(new ConfirmaLance($user, '', 'Confirmamos seu lance!', $leilaoInfo->valorLance, $lote->leilao['titulo']));

        Mail::cc(getAllEmails())
        ->queue(new ConfirmaLance($user, 'layout.email.confirma-lance-adm', 'Novo lance enviado!', $leilaoInfo->valorLance, $lote->leilao['titulo']));

        $lanceAutomatico = new LanceAutomatico();
        $temLanceAutomatico = $lanceAutomatico->where('idcadastro', $user->codigo)->where('idlote', $codigo)->get();
        $permiteLanceAutomatico = true;
        if ($temLanceAutomatico->count() > 0) {
            $permiteLanceAutomatico = false;
        }
        return redirect()->back()->with(['success' => 'Lance efetuado com sucesso!', 'lance_automatico' => $permiteLanceAutomatico]);
    }

    public function defineLanceAutomatico(Request $request) {
        $user = Auth::user();
        $valor = $request->limite_auto;
        $valor = str_replace(",", ".", str_replace(".", "", $valor));
        $lote = (int) $request->lote;
        $lanceAutomatico = new LanceAutomatico();
        $lanceAutomatico->valor = $valor;
        $lanceAutomatico->idlote = $lote;
        $lanceAutomatico->idcadastro = $user->codigo;
        $lanceAutomatico->save();
        
        return redirect()->back()->with('success', 'Você configurou com sucesso lances automáticos para este leilão!');
    }
}