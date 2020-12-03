<?php

namespace App\Http\Middleware;
use Auth;
use App\CadastrosHabilitados;
use App\Leiloes;
use App\Lotes;
use Closure;

class AddLance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public $attributes;
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $time = time();
        
        $codigo = (int) $request->route('codigo');
        $habilitado = CadastrosHabilitados::where('idcadastro', $user->codigo)->where('idlote', $codigo)->get();
        
        if ($habilitado->count() == 0) {
            return redirect()->back()->withErrors(['msg' => 'Você ainda não está habilitado(a) para dar lances neste leilão']);
        }
        
        $lote = Lotes::with('leilao', 'maiorLance')->findOrFail($codigo);
        $leilao = Leiloes::find($lote->leilao['codigo']);
        $lanceData1 = $lote->lance_data_1;
        $lanceData2 = $lote->lance_data_2;
        $dataPrimeiraPraca = strtotime($leilao->leilao_data_final . ' ' . $leilao->leilao_hora_final);
        $dataSegundaPraca = strtotime($leilao->leilao2_data_final . ' ' . $leilao->leilao2_hora_final);

        if ($leilao->tipo == 4) {
            return redirect()->back()->with('success', 'Apenas propostas de compra são permitidas nesta oportunidade.');
        }
        $valorLance = str_replace(",", ".", str_replace(".", "", $request->valor_lance));
        
        if(!$valorLance) {
            $valorLance = 0;
        }
        $lanceMinimoPermitido  = @$lote->maiorLance[0]->valor + $lote->incremento;

        if (!isset($lote->maiorLance[0]->valor)) {
            $lanceMinimoPermitido = $lanceData2;
            if ($dataPrimeiraPraca > $time) {
                $lanceMinimoPermitido = $lanceData1;
            }
        }
       
        if ($valorLance < $lanceMinimoPermitido) {
            return redirect()->back()->withErrors(['msg' => 'O lance efetuado para este leilão é menor ou igual ao lance mínimo de R$ '.number_format($lanceMinimoPermitido, 2, ',', '.').'!']);
        }
        $request->attributes->add(['leilao' => $leilao, 'lote' => $lote, 'valorLance' => $valorLance]);
        return $next($request);
    }
}
