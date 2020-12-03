<?php

namespace App\Routines;

use App\Lotes;
use App\Leiloes;
use App\Lances;
use App\LancesParcelados;
use App\Jobs\LanceAutomatico;

class LanceRoutine 
{
    public function addLance($leilaoInfo, $codigo, $idUsuario) {
        
        $lote = $leilaoInfo->lote;
        $indCronometro = 0;
        if ($this->addMinutes($lote)) {
            $indCronometro = 1;
        }
        
        $lance = new Lances;
        $lance->idcadastro = $idUsuario;
        $lance->desativado = 0;
        $lance->idlote = $codigo;
        $lance->valor = $leilaoInfo->valorLance;
        $lance->data_lance = date('Y-m-d H:i:s');
        $lance->data_cronometro =  date('Y-m-d H:i:s');
        $lance->ind_cronometro = $indCronometro;
        if (isset($leilaoInfo->lance_automatico)) {
            $lance->automatico = 1;
        }
        $lance->save();

        if (!isset($leilaoInfo->automatico)) {
            LanceAutomatico::dispatch($lance)->delay(now()->addMinutes(1));
        }
    
        return true;
    }

    public function addMinutes(Lotes $lote) {
        $mktimeHoje = time();
        $primeiroInicio = strtotime($lote->leilao['leilao_data_inicial'] . ' ' . $lote->leilao['leilao_hora_inicial']);
        $timeLeilao1 = strtotime($lote->leilao['leilao_data_final'] . ' ' . $lote->leilao['leilao_hora_final']);
        $timeLeilao2 = strtotime($lote->leilao['leilao2_data_final'] . ' ' . $lote->leilao['leilao2_hora_final']);
        
        $atualiza5Minutos = 'leilao_data_final';
        $atualizaHora5Min = 'leilao_hora_final';
        $minutosRestantes = $timeLeilao1 - $mktimeHoje;
        $incrementaMinuto = $timeLeilao1 + 180;
        $segundaPraca = false;
        if ($mktimeHoje >= $timeLeilao1) {
            $atualiza5Minutos = 'leilao2_data_final';
            $atualizaHora5Min = 'leilao2_hora_final';
            $minutosRestantes = $timeLeilao2 - $mktimeHoje;
            $incrementaMinuto = $timeLeilao2 + 180;
            $segundaPraca = true;
        }
        
        if ($minutosRestantes <= 180 && $minutosRestantes > 0) {

            $leilao = Leiloes::find($lote->leilao['codigo']);
            
            if ($modelLeilao->tipo != 4 || (int) $modelLeilao->usar_cronometro > 0) {
                $script = __DIR__ . '/../../scripts/encerra-leilao.php ' . (int) $lote->codigo;
                file_put_contents('/tmp/comando', "/usr/bin/php  $script");
                $execResult = exec("nohup nice -20 /usr/bin/php  $script > /dev/null & echo $!");
            }

            $addTime = 180 - $minutosRestantes;
            $incrementaMinuto = $timeLeilao1 + $addTime;
            if ($segundaPraca) {
                $incrementaMinuto = $timeLeilao2 + $addTime;
            }

            $leilao->$atualiza5Minutos = date('Y-m-d', $incrementaMinuto);
            $leilao->$atualizaHora5Min = date('H:i:s', $incrementaMinuto);

            //se for praça única, iguala as datas
            if ($timeLeilao1 == $timeLeilao2) {
                $incrementaMinuto = $timeLeilao1 + $addTime;
                $leilao->leilao_data_final = date('Y-m-d', $incrementaMinuto);
                $leilao->leilao_hora_final = date('H:i:s', $incrementaMinuto);
                $leilao->leilao2_data_final = date('Y-m-d', $incrementaMinuto);
                $leilao->leilao2_hora_final = date('H:i:s', $incrementaMinuto);
            }
            $lote->fechamento = date('d/m/Y H:i:s', strtotime($leilao->$atualiza5Minutos . ' ' . $leilao->$atualizaHora5Min));
            $lote->save();
            $leilao->save();
            return true;
        }
        return false;
    }
}