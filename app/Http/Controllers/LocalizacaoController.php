<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Localizacao;

class LocalizacaoController extends Controller
{
    /**
     * Show the text.
     *
     * @param  int  $id
     * @return View
     */
    public function index($textoTipo = '')
    {
        
        $texto = Localizacao::first();
        
        return view('admin.textos.localizacao', ['vet' => $texto]);
    }

    public function save(Request $request)
    {
        $input = $request->all();
        $localizacao = new Localizacao();
        if ($input['codigo'] > 0) {
            $localizacao = Localizacao::find($input['codigo']);
        }
        $localizacao->endereco = $input['endereco'];
        $localizacao->email = $input['email'];
        $localizacao->whatsapp = $input['whatsapp'];
        $localizacao->telefone = $input['telefone'];
        if (Cache::has('localizacao')) {
            Cache::forget('localizacao');
        }
        $localizacao->save();
        return redirect()->back()->with('success', 'Operação concluída');
    }
    
}