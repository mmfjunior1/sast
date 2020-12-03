<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cidades;
use App\Lotes;

class CidadesController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function getCities(Request $request)
    {
        $cidades = Cidades::where('idestado', $request->idestado)->get();
        return view('admin.cidades.combo', ['idcidade' => null,  'cidades' => $cidades]);
    }

    /**
     * Pega as cidades disponíveis de acordo com os lotes existentes
     *
     * @param  int  $id
     * @return View
     */
    public function getCitiesLots(Request $request)
    {
        $lotes = Lotes::select('idcidade')->distinct()->where('idestado', $request->idestado)->get();
        $cidades = [];
        if ($lotes->count() > 0) {
            $cidades = $lotes->pluck('idcidade')->toArray();
        }
        $cidades = Cidades::where('idestado', $request->idestado)
                           ->whereIn('codigo', $cidades)->get();
        
        return view('admin.cidades.combo', ['idcidade' => null,  'cidades' => $cidades]);
    }

    /**
     * Pega as cidades disponíveis de acordo com os lotes existentes
     *
     * @param  Request  $request
     * @return View
     */
    public function getNeighborhood(Request $request)
    {
        $estado = $request->idestado;
        $estado = explode("|", $estado);
        $bairros = Lotes::select('bairro')->distinct()->where('idestado', (int) $estado[1]);
        $idCidade = trim($request->idcidade);
        if ($idCidade != '') {
            $idCidade = explode("|", $idCidade);
            $idCidade = $idCidade[1];
        }
        if ($idCidade > 0) {
            $bairros = $bairros->where('idcidade', $idCidade);
        }
        $bairros = $bairros->orderBy('bairro');
        $bairros = $bairros->get();
        return view('admin.cidades.combo-bairro', ['bairros' => $bairros]);
    }
    
}