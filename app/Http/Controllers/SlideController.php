<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Slides;
use Illuminate\Support\Facades\Storage;

class SlideController extends Controller
{
    private $request;
    
    public function index(Request $request) {
     //   echo storage_path('app/public');die;
        $slides = Slides::all();
        return view('admin.slides.slides', ['vet' => $slides]);
    }

    public function delete(Request $request, $id) {
        $slide = Slides::findOrFail($id);
        Storage::delete('public/imagens/' . $slide->imagem);
        $slide->delete();
        return redirect('slides');
    }

    public function save(Request $request) {
        $input = $request->all();
        $codigo = 0;
        if (isset($input['codigo'])) {
            $codigo = (int) $input['codigo'];
        }
        $slide = new Slides;
        $delete = false;
        if ($codigo > 0) {
            $slide = Slides::findOrFail($codigo);
            $delete = true;
        }
        $slide->url = $input['url'];
        if ($request->imagem) {
            if ($delete) {
                Storage::delete('public/imagens/' . $slide->imagem);
            }
            $ext = $request->imagem->getClientOriginalExtension();
            $slide->imagem   = md5($request->imagem->getClientOriginalName() . time()) . '.' . $ext;
            $request->imagem->storeAs('public/imagens', $slide->imagem);

            $img = storage_path('app/public') . '/imagens/' . $slide->imagem;
            redimencionaImagem($img, 1920, 500);
        }
        $slide->save();
        return redirect('slides');
    }
}