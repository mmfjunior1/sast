<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\NewsLetter as NewsLetterValidate;
use App\NewsLetter;
use App\Utils\SendMail;

class NewsLetterController extends Controller
{
    public function save(NewsLetterValidate $request)
    {
        $news = new NewsLetter;
        $news->nome = $request->nome;
        $news->email = $request->email;
        $news->save();

        return redirect()->back()->with('success', 'Email cadastrado com sucesso!');
    } 

    public function listaEmails(Request $request) 
    {
        $lista = NewsLetter::all();
        return view('admin.newsletter.newsletter', ['cads' => $lista]);
    }

    public function excluirEmail($codigo) 
    {
        $email = NewsLetter::findOrFail($codigo);
        $email->delete();
        return redirect('newsletter')->with('success', 'Email excluído com sucesso');
    }
}