<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Lotes;
use Auth;
use App\CadastrosHabilitados;
use App\CadastrosDocumentos;
use App\Routines\EncerramentoLeilao;
use App\Routines\AberturaLeilao;

class LotesSiteController extends Controller
{
    private function getInfoLote($request, $id, $user) {
        $lote = Lotes::with([
            'imagensLote', 
            'leilao', 
            'anexosLote', 
            'maiorLance',
            'maiorLanceParcelado',
            'lanceParceladoEscolhido'])->findOrFail($id);
        if ($lote['leilao']->suspender == 1 ) {
            return redirect('/')->with('success', 'Este registro não foi encontrado');
        }
        $leiloes = \Illuminate\Support\Facades\DB::select('
        select * from  (
            select * from (
                    SELECT DISTINCT A.*,  B.categoria
                    FROM leiloes A
                    INNER JOIN lotes B ON A.codigo = B.idleilao AND B.codigo !=  ' . $id . ' 
                    WHERE B.idestado = ' . (int) $lote->idestado . ' 
                    AND B.idcidade = ' . (int) $lote->idcidade . ' 
                    AND B.bairro = \'' .$lote->bairro .'\'
                    AND (A.encerrado = 1  or A.encerrado = 7)
                    AND B.categoria = ' . $lote->categoria . ' 
                    
                    order by A.leilao2_data_final asc, A.leilao2_hora_final asc, B.categoria asc limit 8)
                as first
            union    
            select * from (
                SELECT DISTINCT A.*,  B.categoria
                FROM leiloes A
                INNER JOIN lotes B ON A.codigo = B.idleilao AND B.codigo !=  ' . $id . ' 
                WHERE B.idestado = ' . (int) $lote->idestado . ' 
                AND B.idcidade = ' . (int) $lote->idcidade . ' 
                AND B.bairro != \'' .$lote->bairro .'\'
                AND (A.encerrado = 1  or A.encerrado = 7)
                AND B.categoria = ' . $lote->categoria . ' 
                
                order by A.leilao2_data_final asc, A.leilao2_hora_final asc, B.categoria asc limit 8)
            as sec)
            as todos');
        
        if (count($leiloes) == 0) {
            $leiloes = leiloesDestaque();
        }

        $estaHabilitado = CadastrosHabilitados::where('idcadastro', @$user->codigo)
            ->where('idlote', $id)
            ->get()->count();
        
        return ['today' => getdate(), 'dataCronometro' => retornaDataCronometro($lote->leilao, 1), 'md5'=> md5($lote), 'lote' => $lote,'estaHabilitado' => $estaHabilitado, 'user' => Auth::user(), 'request' => $request, 'leiloes' => $leiloes, 'ultimosLances' => ulimosLances($lote), 'maiorLance' => maiorLance($lote)];
    }
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function lote(Request $request, int $id)
    {
        $user = Auth::user();

        if($request->ajax()) {
            
            return response()->json($this->getInfoLote($request, $id, $user));
        }

        EncerramentoLeilao::encerramento($id);
        AberturaLeilao::abertura($id);

        return view('site.leiloes.lote', $this->getInfoLote($request, $id, $user));
    }

    public function lotes(Request $request, int $id)
    {
        $id = (int) $id;
        $lotes = Lotes::with('leilao', 'maiorLance.usuarios', 'maiorLanceParcelado', 'imagensLote')
                        ->where('idleilao', $id)
                        ->orderBy('lote_destaque', 'asc')
                        ->get();
       
        return view('site.leiloes.lotes', ['lotes' => $lotes, 'user' => Auth::user(), 'request' => $request]);
    }

    public function habilitarLeilao(Request $request, $id): ?\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $lote = Lotes::findOrFail($id);
        if (!$user) {
            return redirect()->back()->with('success', 'Para habilitar-se em um leilão entre com seus dados cadastrados!');
        }
        
        if (CadastrosHabilitados::where('idcadastro', $user->codigo)
                                 ->where('idlote', $id)
                                 ->get()->count() > 0) {
            return redirect()->back()->with('success', 'VOCÊ JÁ ESTÁ HABILITADO (A) PARA OFERTAR LANCES NESTE LEILÃO!');
        }
        
        $documentos = CadastrosDocumentos::where('idcadastro', $user->codigo)
                                           ->where('status_pessoal', 1)
                                           ->where('status_residencia', 1)
                                           ->get();
        if ($documentos->count() == 0) {
            return redirect()->back()->with('success', 'Para habilitar-se, acesse a sua área restrita e envie seus documentos acessando: MINHA CONTA > CADASTRO > DOCUMENTOS. Após o envio aguarde aprovação de nossa equipe.');
        }
        
        CadastrosHabilitados::updateOrCreate(['idcadastro' => $user->codigo, 'idlote' => $lote->codigo],
                                             ['idlote' => $lote->codigo,
                                              'idcadastro' => $user->codigo,
                                              'data_habilitacao' => date('Y-m-d H:i:s')
                                             ]
        );
        return redirect()->back()->with('success', 'VOCÊ JÁ ESTÁ HABILITADO (A) PARA OFERTAR LANCES NESTE LEILÃO!');
    }

    public function rotinasLeilao(Request $request) {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ip != '127.0.0.1') {
            return 'false';
        }
        EncerramentoLeilao::encerramento(0);
        AberturaLeilao::abertura(0);
        return 'true';
    }
}