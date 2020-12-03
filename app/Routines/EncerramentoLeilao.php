<?php

namespace App\Routines;

use Illuminate\Support\Facades\Log;
use App\Leiloes;
use App\Lotes;
use App\ConfigEmail;
use App\Cadastros;
use App\Mail\ComunicaVencedor;
use Illuminate\Support\Facades\Mail;
class EncerramentoLeilao 
{
    public static function encerramento($lote, $cli = 0) {
        $lote = (int) $lote;
        $texto = ConfigEmail::where('email_tipo', 'resultado_leilao' )->get();
        $strNome = '';
        $textBody = 'Prezado(a) Senhor(a) {{str_nome}},<br><br>';
        $textBody .= 'Temos a satisfação de intima-lo que seu lance foi o vencedor do leilão em apreço.<br><br>';
        $textBody .= 'Dentro de instantes V.Sa. receberá maiores informações sobre o procedimento a ser seguido, bem como as informações para pagamento do valor da arrematação e da comissão do leiloeiro.<br><br>';
        $textBody .= 'Atenciosamente,<br>';
        $textBody .= 'Globo Leilões - Responsabilidade Social e Ambiental<br>';
        $textBody .= 'Tel: 55 11 3181-6109 / 11  9-4490-6874<br />';
        $textBody .= 'Avenida Paulista, n° 1079 - 7º e 8º andar<br>';
        $textBody .= 'Bela Vista - São Paulo - SP<br>';
        $textBody .= 'www.globoleiloes.com.br';

        $textoGravado = $textBody;
        $textoGravado1 = 'Prezado(a) Senhor(a) {{str_nome}},<br><br>';
        $textoGravado1 .= 'Temos a satisfação de intima-lo que sua Proposta de Compra será encaminhada para apreciação judicial.<br><br>';
        $textoGravado1 .= 'Comunicaremos V.Sa. da manifestação judicial correspondente.<br><br>';
        $textoGravado1 .= 'Havendo aceitação judicial de sua Proposta de Compra, V.Sa. receberá maiores informações sobre o procedimento a ser seguido para formalizar a arrematação em apreço, bem como receberá informações para pagamento do valor da arrematação e da comissão do leiloeiro.<br><br>';
        $textoGravado1 .= 'Atenciosamente,<br>';
        $textoGravado1 .= 'Globo Leilões - Responsabilidade Social e Ambiental<br>';
        $textoGravado1 .= 'Tel: 55 11 3181-6109 / 11  9-4490-6874<br />';
        $textoGravado1 .= 'Avenida Paulista, n° 1079 - 7º e 8º andar<br>';
        $textoGravado1 .= 'Bela Vista - São Paulo - SP<br>';
        $textoGravado1 .= 'www.globoleiloes.com.br';
        
        if ($lote > 0) {

            Log::info('Encerramento de leilões: Iniciando');
            
            $lote = Lotes::with('maiorLance', 'maiorLanceParcelado')->find($lote);
            
            if ($lote->encerrado != '1') {
                if ($cli > 0) {
                    unlink('/tmp/lote-encerra' . $lote->codigo);
                    die("AAAAAAAA" .  __LINE__);
                    exit;
                }
                return true;
            }

            if ($texto->count() > 0) {
                $textoGravado = $texto[0]->texto;
                $textoGravado1 = trim($texto[1]->texto) != '' ? $texto[1]->texto : $textoGravado1;
            }
            
            $maiorLance = $lote->maiorLance;
            $maiorLanceParcelado = $lote->maiorLanceParcelado;
            

            $leilao = $lote->idleilao;
            $modelLeilao = Leiloes::find($leilao);
            
            if ($modelLeilao->tipo == 4 && (int) $modelLeilao->usar_cronometro == 0) {
                if ($cli > 0) {
                    unlink('/tmp/lote-encerra' . $lote->codigo);
                    exit;
                }
                return true;
            }
            // Se a data inicial 2 ainda for futura, coloca o leilão em futuro
            $data1 = strtotime($modelLeilao->leilao_data_final . ' ' . $modelLeilao->leilao_hora_final);
            $data2 = strtotime($modelLeilao->leilao2_data_inicial . ' ' . $modelLeilao->leilao2_hora_inicial);
            
            if ($data2 > time() && time() > $data1) {
                $lote->encerrado = 7;
                $lote->save();
                $modelLeilao->encerrado = 7;
                $modelLeilao->save();
                Log::info('Encerramento de leilão: Voltando o leilão ao status futuro. Segunda data inicial é maior ' . $lote->codigo);
                
                return true;
            }
            $primeiraPraca = strtotime($modelLeilao->leilao_data_final . ' ' . $modelLeilao->leilao_hora_final);
            
            $primeiraPraca  = time() - $primeiraPraca  <=  3 ? true : false;
            //se ainda estivermos na primeira praca e houver lance, vamos encerrar o leilao
            $dataInicial = strtotime($modelLeilao->leilao2_data_final . ' ' . $modelLeilao->leilao2_hora_final);
            if ($primeiraPraca && isset($lote->maiorLance[0])) {
                $dataInicial = strtotime($modelLeilao->leilao_data_final . ' ' . $modelLeilao->leilao_hora_final);
            }
            
            if (time() > $dataInicial) {
                
                if (isset($lote->maiorLance[0])) {
                    $cadastro = Cadastros::find($lote->maiorLance[0]->idcadastro);
                    $strNome = trim($cadastro->nome) != '' ? $cadastro->nome : $cadastro->razao_social;
                    preg_match_all('/(\{\{str_nome\}\})/', $textoGravado, $saida, PREG_PATTERN_ORDER);
                    $saida = array_unique($saida[0]);
                    $arr = array('{{str_nome}}' => $strNome,);
                    foreach($saida as $value) {
                            $var = $arr[$value];
                            $textoGravado = str_replace($value, $var, $textoGravado);
                    }
                    $textBody = $textoGravado;
                    
                    Mail::to($cadastro->email)
                    ->queue(new ComunicaVencedor('Lance Vencedor', $textBody));
                }
                
                if (!isset($lote->maiorLance[0]) && isset($lote->maiorLanceParcelado[0])) {
                    $cadastro = Cadastros::find($lote->maiorLanceParcelado[0]->idcadastro);
                    $strNome = trim($cadastro->nome) != '' ? $cadastro->nome : $cadastro->razao_social;
                    preg_match_all('/(\{\{str_nome\}\})/', $textoGravado1, $saida, PREG_PATTERN_ORDER);
                    $saida = array_unique($saida[0]);
                    $arr = array('{{str_nome}}' => $strNome,);
                    foreach($saida as $value) {
                            $var = $arr[$value];
                            $textoGravado1 = str_replace($value, $var, $textoGravado1);
                    }
                    $textBody = $textoGravado1;
                    
                    Mail::to($cadastro->email)
                    ->queue(new ComunicaVencedor('Proposta de Compra Encaminhada para Apreciação Judicial', $textBody));
                }
                $statusEncerrado = $maiorLance->count() > 0 || $maiorLanceParcelado->count() > 0 ? 8 : 6;
                $modelLeilao->encerrado = $statusEncerrado;
                $modelLeilao->save();
                $lote->encerrado = $statusEncerrado;
                $lote->save();
                Log::info('Encerramento de leilão: Encerrando lote ' . $lote->codigo);
                if ($cli > 0) {
                    unlink('/tmp/lote-encerra' . $lote->codigo);
                    exit;
                }
                return true;
            }
            Log::info('Encerramento de leilões: Lote ainda não será encerrado ' . $lote->codigo . '=> Encerramento em ' . $lote->fechamento);
            
            return true;
        }
        Log::info('Encerramento de leilões: CRON - Iniciando');
        
        $leiloes = Leiloes::where('encerrado', 1)->get();
        $qtdLeilões = 0;
        foreach($leiloes as $leilao) {
            $lotes = Lotes::with('maiorLance', 'maiorLanceParcelado')->where('idleilao', '=', $leilao->codigo)->get();

            $dataInicial = strtotime($leilao->leilao2_data_final . ' ' . $leilao->leilao2_hora_final);

            $primeiraPraca = strtotime($leilao->leilao_data_final . ' ' . $leilao->leilao_hora_final);
            
            $primeiraPraca  = time() - $primeiraPraca  <=  3 ? true : false;
            //se ainda estivermos na primeira praca e houver lance, vamos encerrar o leilao
            $dataInicial = strtotime($leilao->leilao2_data_final . ' ' . $leilao->leilao2_hora_final);
            if ($primeiraPraca) {
                foreach($lotes as $lote) {
                    if (isset($lote->maiorLance[0])) {
                        $dataInicial = strtotime($leilao->leilao_data_final . ' ' . $leilao->leilao_hora_final);
                        break;
                    }
                }
            }
            $modelLeilao = Leiloes::find($leilao->codigo);
            // Se a data inicial 2 ainda for futura, coloca o leilão em futuro
            $data1 = strtotime($modelLeilao->leilao_data_final . ' ' . $modelLeilao->leilao_hora_final);
            $data2 = strtotime($modelLeilao->leilao2_data_inicial . ' ' . $modelLeilao->leilao2_hora_inicial);
            if ($data2 > time() && time() > $data1) {
                $lotes = Lotes::where('idleilao', $leilao->codigo)->update(['encerrado' => 7]);
                $modelLeilao->encerrado = 7;
                $modelLeilao->save();
                Log::info('Encerramento de leilão: Voltando o leilão ao status futuro. Segunda data inicial é maior que a data atual. Leilão' . $leilao->codigo);
                continue;
            }
            $statusEncerrado = 6;
            if (time() > $dataInicial) {
                foreach($lotes as $lote){
                    if ($lote->encerrado != 1) {
                        continue;
                    }
                    if ($leilao->tipo == 4 && $leilao->usar_cronometro == 0) {
                        
                        $statusEncerrado = 1;
                        continue;
                    }
                    
                    if (isset($lote->maiorLance[0])) {
                        $cadastro = Cadastros::find($lote->maiorLance[0]->idcadastro);
                        $strNome = trim($cadastro->nome) != '' ? $cadastro->nome : $cadastro->razao_social;
                        preg_match_all('/(\{\{str_nome\}\})/', $textoGravado, $saida, PREG_PATTERN_ORDER);
                        $saida = array_unique($saida[0]);
                        $arr = array('{{str_nome}}' => $strNome,);
                        foreach($saida as $value) {
                                $var = $arr[$value];
                                $textoGravado = str_replace($value, $var, $textoGravado);
                        }
                        $textBody = $textoGravado;
                        
                        Mail::to($cadastro->email)
                        ->queue(new ComunicaVencedor('Lance Vencedor', $textBody));
                    }
                    
                    if (!isset($lote->maiorLance[0]) && isset($lote->maiorLanceParcelado[0])) {
                        $cadastro = Cadastros::find($lote->maiorLanceParcelado[0]->idcadastro);
                        $strNome = trim($cadastro->nome) != '' ? $cadastro->nome : $cadastro->razao_social;
                        preg_match_all('/(\{\{str_nome\}\})/', $textoGravado1, $saida, PREG_PATTERN_ORDER);
                        $saida = array_unique($saida[0]);
                        $arr = array('{{str_nome}}' => $strNome,);
                        foreach($saida as $value) {
                                $var = $arr[$value];
                                $textoGravado1 = str_replace($value, $var, $textoGravado1);
                        }
                        $textBody = $textoGravado1;
                        
                        Mail::to($cadastro->email)
                        ->queue(new ComunicaVencedor('Proposta de Compra Encaminhada para Apreciação Judicial', $textBody));
                    }
                    $maiorLance = $lote->maiorLance;
                    $maiorLanceParcelado = $lote->maiorLanceParcelado;
                    $statusEncerrado = $maiorLance->count() > 0 || $maiorLanceParcelado->count() > 0 ? 8 : 6;
                    
                    $modelLeilao->encerrado = $statusEncerrado;
                    $modelLeilao->save();
                }
                $qtdLeilões++;
                Lotes::where('encerrado', 1)->where('idleilao', $leilao->codigo)->update(['encerrado' => $statusEncerrado]);
            }
            continue;
        }
        Log::info('Encerramento de leilões: Fim. ' . $qtdLeilões . ' leilões afetados');        
    }
}