<?php

namespace App\Http\Middleware;
use Auth;
use App\LanceAutomatico as Automatico;
use App\Lotes;
use Closure;

class LanceAutomatico
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
        $lanceAutomacito = new Automatico();
        $valor = $request->limite_auto;
        $valor = str_replace(",", ".", str_replace(".", "", $valor));
        $valor = floatval($valor);
        if ($valor == 0) {
            return redirect()->back()->with('success', 'O valor do lance automático deve ser maior que 0.');
        }
        $verificaLanceAutomatico = $lanceAutomacito->where('idcadastro', $user->codigo)->where('idlote', $request->lote)->get();
        if ($verificaLanceAutomatico->count() > 0) {
            $complemento = "Caso seu lance automático seja vencido por outro lance, você será informado e poderá configurar um novo lance.";
            return redirect()->back()->with('success', 'Você já configurou lance automático para este leilão. <br>' . $complemento);
        }
        $lotesModel = new Lotes;
        $loets = $lotesModel->findOrFail($request->lote);
        return $next($request);
    }
}
