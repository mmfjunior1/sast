<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\TiposLeilao;
use Illuminate\Http\Request;

class TiposLeilaoController extends Controller
{
    /**
     * Show the text.
     *
     * @param  int  $id
     * @return View
     */
    public function index($textoTipo = '')
    {
        $texto = TiposLeilao::all();
        return view('admin.textos.tipos-leilao' , ['vet' => $texto]);
    }

    public function save(Request $request)
    {
        $input = $request->all();
        $modelTipo1 = TiposLeilao::where('tipo', 1)->first();
        if (!$modelTipo1) {
            $modelTipo1 = new TiposLeilao();
            $modelTipo1->tipo = 1;
        }
        $modelTipo1->texto = $input['texto1'];
        $modelTipo1->save();

        $modelTipo2 = TiposLeilao::where('tipo', 2)->first();
        if (!$modelTipo2) {
            $modelTipo2 = new TiposLeilao();
            $modelTipo2->tipo = 1;
        }
        $modelTipo2->texto = $input['texto2'];
        $modelTipo2->save();
        
        return redirect()->back()->with('success', 'Operação concluída');
    }

    private function _returnModel($textoTipo)
    {
        switch($textoTipo) {
            case 'quem-somos':
            return new \App\QuemSomos();
            break;
        }
    }
}