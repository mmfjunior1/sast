<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Materias;

class MateriasController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        return view('admin.materias.index', ['vet' => User::findOrFail($id), 'title' => 'Blog']);
    }
    /**
     * List all Blogs.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function index(Request $request)
    {
        $vet = Materias::all();
        
        return view('admin.materias.index', ['materias' => $vet, 'title' => 'Blog']);
    }

    

    /**
     * Update the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function save(Request $request)
    {
        $input = $request->all();
        
        $cadastro = new Materias();
        $input['codigo'] = (int) $input['codigo'];
        $cadastro->data = date('Y-m-d') ;
        if ($input['codigo'] > 0) {
            $cadastro = Materias::find($input['codigo']);
            unset($cadastro->data);
        }
        $cadastro->idcategoria = $input['idcategoria'];
        $cadastro->titulo = $input['titulo'];
        $cadastro->subtitulo = $input['subtitulo'];
        $cadastro->texto = $input['texto'];
        $cadastro->data = formataData($input['data']);
        
        if ($request->imagem) {
            $ext = explode('.', $request->imagem->getClientOriginalName());
            $qtdIndex = count($ext);
            $ext = $ext[$qtdIndex - 1];
            $cadastro->imagem     = md5($request->imagem->getClientOriginalName()) . '.' . $ext;
            $img = storage_path('app/public') . '/imagens/'.$cadastro->imagem;
            $imgThumb = storage_path('app/public') . '/imagens/thumb/'.$cadastro->imagem;
            
            $request->imagem->storeAs('public/imagens', ''.$cadastro->imagem);
            $request->imagem->storeAs('public/imagens/thumb', ''.$cadastro->imagem);
            redimencionaImagem($img, 705, 455);
            redimencionaImagem($imgThumb, 100, 100);
        }
        $cadastro->save();
        return redirect('/materias')->with('success', 'Operação concluída');
    }

    /**
     * Delete the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function delete($id)
    {
        $id = (int) $id;
        $cadastro = Materias::findOrFail($id);
        $cadastro->delete();
        return redirect('/materias')->with('success', 'Operação concluída');
    }

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function get($id)
    {
        $id = (int) $id;
        $cadastros = Materias::all();
        $materia = Materias::findOrFail($id);
        return view('admin.materias.index', ['materias' => $cadastros, 'vet' => $materia, 'title' => ucwords($materia->titulo)]);
    }

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function listaMaterias()
    {
        $cadastros = Materias::all();
        return view('blog.lista-materias', ['materias' => $cadastros, 'title' => 'Blog']);
    }

    public function lerMateria($id)
    {
        $materia = Materias::findOrFail($id);
        return view('blog.materia', ['materia' => $materia, 'title' => ucwords($materia->titulo)]);
    }
}