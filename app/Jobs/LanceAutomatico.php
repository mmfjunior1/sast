<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Routines\LanceRoutine;
use App\Lances;
use App\LanceAutomatico as LanceAutomaticoModel;
use App\Lotes;
use App\Leiloes;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailGenerico;
use App\UserSite;

class LanceAutomatico implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $lances;
    private $usuario;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Lances $lances)
    {
        //
        $this->lances = $lances;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lancesAutomaticos = LanceAutomaticoModel::where('idlote', $this->lances->idlote)
        ->get();
        if ($lancesAutomaticos->count() == 0) {
            return true;
        }
        foreach ($lancesAutomaticos as $lanceAutomatico) {
            $lote = Lotes::with('leilao', 'maiorLance')->findOrFail($this->lances->idlote);
            $leilao = Leiloes::find($lote['leilao']->codigo);
            if ($lote['maiorLance'][0]->idcadastro == $lanceAutomatico->idcadastro) {
                continue;
            }
            $leilaoInfo = new \StdClass();
            $leilaoInfo->leilao = $leilao;
            $leilaoInfo->lote = $lote;
            $leilaoInfo->lance_automatico = 1;
            $leilaoInfo->valorLance = $lote['maiorLance'][0]->valor + $lote->incremento;
            if ($lanceAutomatico->valor < $leilaoInfo->valorLance) {
                LanceAutomaticoModel::where('codigo', '=', $lanceAutomatico->codigo)->delete();
                $usuario = new UserSite();
                $user = $usuario->find($lanceAutomatico->idcadastro);
                $appurl = env('APP_URL');
                $url = '<a href="'.$appurl.'/leilao/lote/'.$this->lances->idlote.'/'.urlTitulo($leilao->titulo).'">ESTE LEILÃO</a>';
                $nome = $user->nome != '' ? $user->nome : $user->razao_socal;
                $texto = '<p>Caro(a) ' . $nome . '</p>';
                $texto .= '<p>Seu lance automático foi superado por outro usuário do site.</p>';
                $texto .= '<p>Para não perder a oportunidade, acesse ' . $url . ' e oferte um novo lance!</p>';
                $texto .= '<p>Atenciosamente,</p>';
                $texto .= 'Globo Leilões - Responsabilidade Social e Ambiental<br>';
                $texto .= 'Tel: 55 11 3181-6109 / 11  9-4490-6874<br />';
                $texto .= 'Avenida Paulista, n° 1079 - 7º e 8º andar<br>';
                $texto .= 'Bela Vista - São Paulo - SP<br>';
                $texto .= 'www.globoleiloes.com.br';
                Mail::to($user->email)
                ->queue(new EmailGenerico('Seu lance automático foi superado por outro usuário', $texto));
                return false;
            }
            $lanceRoutine = new LanceRoutine();
            $lanceRoutine->addLance($leilaoInfo, $this->lances->idlote, $lanceAutomatico->idcadastro);
        }
        
    }
}
