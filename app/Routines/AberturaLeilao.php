<?php

namespace App\Routines;

use App\Leiloes;
use App\Lotes;
use Illuminate\Support\Facades\Log;

class AberturaLeilao 
{
    public static function abertura($lote) {
        $lote = (int) $lote;
        Log::info('Abertura de leilões: Iniciando');
        if ($lote > 0) {
            $lote = Lotes::find($lote);
            if ($lote->encerrado != 7) {
                Log::info('Abertura de leilões: O lote ' . $lote->codigo . ' não será aberto. Seu status atual não permite. Status ' . $lote->encerrado);
                return true;
            }
            $leilao = $lote->idleilao;
            $modelLeilao = Leiloes::find($leilao);
            $dataInicial = strtotime($modelLeilao->leilao_data_inicial . ' ' . $modelLeilao->leilao_hora_inicial);
            
            if ($dataInicial <= time()) {
                $modelLeilao->encerrado = 1;
                $modelLeilao->save();
                $lote->encerrado = 1;
                $lote->save();
                Log::info('Abertura de leilões: Abrindo lote ' . $lote->codigo);
                return true;
            }
            Log::info('Abertura de leilões: Lote ainda não será aberto ' . $lote->codigo . '=> Abertura em ' . $lote->abertura);
            return true;
        }


        $leiloes = Leiloes::where('encerrado', 7)->where('leilao_data_inicial', '=', date('Y-m-d'))->get();
        
        $qtdLeilões = 0;
        foreach($leiloes as $leilao) {
            $dataInicial = strtotime($leilao->leilao_data_inicial . ' ' . $leilao->leilao_hora_inicial);
            if ($dataInicial <= time()) {
                $modelLeilao = Leiloes::find($leilao->codigo);
                $modelLeilao->encerrado = 1;
                $modelLeilao->save();
                $qtdLeilões++;
                Lotes::where('encerrado', 7)->where('idleilao', $leilao->codigo)->update(['encerrado' =>1]);
            }
            continue;
        }
        Log::info('Abertura de leilões: Fim. ' . $qtdLeilões . ' leilões afetados');        
    }
}