<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Lotes;

class HabilitadosLoteController extends Controller
{
    /**
     * List all profiles.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function index()
    {
        $vet = Lotes::distinct()->get(['codigo', 'titulo']);
        return view('admin.habilitados_lotes.index', ['vet' => $vet, ]);
    }

    public function search(Request $request) {
        $input = $request->all();
        $vet = Lotes::distinct()->get(['codigo', 'titulo']);
        $habilitados = Lotes::find($input['idlote'])->habilitados()->get(['data_habilitacao', 'nome', 'razao_social']);
        return view('admin.habilitados_lotes.index', ['vet' => $vet, 'idlote' => $input['idlote'], 'habilitados' => $habilitados]);
    }

    public function verHabilitados($idlote) {
        $habilitados = Lotes::find($idlote)->habilitados()->get(['data_habilitacao', 'nome', 'razao_social', 'email', 'apelido']);
        
        $titulo = Lotes::findOrFail($idlote)->toArray();
        $titulo = $titulo['titulo'];
        
        return view('admin.habilitados_lotes.lotes-habilitados', ['titulo' => $titulo, 'habilitados' => $habilitados]);
    }
}