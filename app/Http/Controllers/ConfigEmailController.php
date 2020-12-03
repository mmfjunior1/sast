<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ConfigEmail;

class ConfigEmailController extends Controller
{
    private $request;
    private $view;
    private $model = '';
    private $emailContent = null;
    private $initialRecord = '';
    public function __construct(Request $request)
    {
        $uri = $request->path();
        $explodeUri = explode('/', $uri);
        $this->model = new ConfigEmail();
        switch($explodeUri[1]) {
            case 'recebe-lance':
                $this->view = 'recebe-lance';
                $this->emailContent = $this->model->where('email_tipo', 'recebe_lance')->first();
                $this->initialRecord = 'recebe_lance';
            break;
            case 'cadastro-documentos':
                $this->view = 'cadastros-documentos';
                $this->emailContent = $this->model->where('email_tipo', 'cadastro_documentos')->orderBy('id')->get();
                $this->initialRecord = 'cadastro_documentos';
            break;
            case 'confirmacao-cadastro':
                $this->view = 'confirmacao-cadastro';
                $this->emailContent = $this->model->where('email_tipo', 'confirma_cadastro')->first();
                $this->initialRecord = 'confirma_cadastro';
            break;
            case 'resultado-leilao':
                $this->view = 'resultado-leilao';
                $this->emailContent = $this->model->where('email_tipo', 'resultado_leilao')->get();
                $this->initialRecord = 'resultado_leilao';
            break;
        }
    }
    /**
     * Show the text.
     *
     * @param  int  $id
     * @return View
     */
    public function index($textoTipo = '')
    {
        return view('admin.config_email.' . $this->view, ['vet' => $this->emailContent]);
    }

    public function save(Request $request)
    {
        $input = $request->all();
        $texto = new ConfigEmail();
        $input['codigo'] = (int) $input['codigo'];
        if ($input['codigo'] > 0) {
            $texto = ConfigEmail::find($input['codigo']);
            $texto->email_tipo = $this->initialRecord;
        }
        $texto->texto = $input['texto'];
        $texto->save();
        return redirect()->back()->with('success', 'Operação conluída');
    }

    public function saveCadastroDocumentos(Request $request)
    {
        $input = $request->all();
        $codigo1 = (int) $input['codigo1'];
        $codigo2 = (int) $input['codigo2'];
        $codigo3 = (int) $input['codigo3'];

        //salva o primeiro texto
        $texto = new ConfigEmail();
        if ($codigo1 > 0) {
            $texto = ConfigEmail::find($codigo1);
            $texto->email_tipo = $this->initialRecord;
        }
        $texto->texto = $input['texto'];
        $texto->save();

        //salva o segundo texto
        $texto = new ConfigEmail();
        if ($codigo2 > 0) {
            $texto = ConfigEmail::find($codigo2);
            $texto->email_tipo = $this->initialRecord;
        }
        $texto->texto = $input['texto2'];
        $texto->save();

        //salva o terceiro texto
        $texto = new ConfigEmail();
        if ($codigo3 > 0) {
            $texto = ConfigEmail::find($codigo3);
            $texto->email_tipo = $this->initialRecord;
        }
        $texto->texto = $input['texto3'];
        $texto->save();
        return redirect()->back()->with('success', 'Operação conluída');
    }

    public function saveResultadoLeilao(Request $request)
    {
        $input = $request->all();
        $codigo1 = (int) $input['codigo1'];
        $codigo2 = (int) $input['codigo2'];

        //salva o primeiro texto
        $texto = new ConfigEmail();
        if ($codigo1 > 0) {
            $texto = ConfigEmail::find($codigo1);
            $texto->email_tipo = $this->initialRecord;
        }
        $texto->texto = $input['texto'];
        $texto->save();

        //salva o segundo texto
        $texto = new ConfigEmail();
        if ($codigo1 > 0) {
            $texto = ConfigEmail::find($codigo2);
            $texto->email_tipo = $this->initialRecord;
        }
        $texto->texto = $input['texto2'];
        $texto->save();
        return redirect()->back()->with('success', 'Operação conluída');
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