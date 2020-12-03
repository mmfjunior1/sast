<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Cadastros;

class TextosController extends Controller
{
    private $request;
    private $view;
    private $model = '';
    public function __construct(Request $request)
    {
        $uri = $request->path();
        $explodeUri = explode('/', $uri);
        switch($explodeUri[1]) {
            case 'quem-somos':
                $this->view = 'quem-somos';
                $this->model = new \App\QuemSomos();
            break;
            case 'como-participar':
                $this->view = 'como-participar';
                $this->model = new \App\ComoParticipar();
            break;
            case 'nosso-diferencial':
                $this->view = 'nosso-diferencial';
                $this->model = new \App\NossoDiferencial();
            break;
            case 'politica-de-privacidade':
                $this->view = 'politica-de-privacidade';
                $this->model = new \App\PoliticaPrivacidade();
            break;
            case 'termo-de-uso':
                $this->view = 'termo-de-uso';
                $this->model = new \App\TermoUso();
            break;
            case 'judicial':
                $this->view = 'judicial';
                $this->model = new \App\Judicial();
            break;
            case 'extrajudicial':
                $this->view = 'extrajudicial';
                $this->model = new \App\Extrajudicial();
            break;
            
        }
        //echo $request->path();die;
    }
    /**
     * Show the text.
     *
     * @param  int  $id
     * @return View
     */
    public function index($textoTipo = '')
    {
        
        $texto = $this->model->first();
        
        return view('admin.textos.' . $this->view, ['vet' => $texto]);
    }

    public function save(Request $request)
    {
        $input = $request->all();
        
        $this->model = $this->model->first();
        $this->model->texto = $input['texto'];
        $this->model->save();
        return redirect()->back()->with('success', 'Operação concluída.');
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