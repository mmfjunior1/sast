<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\LanceAutomatico;
use App\Slides;
use App\Estados;
use App\Lotes;
use Auth;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $slides = Slides::all();
        
        $leiloes = leiloesDestaque();
        
        return view('site.destaque', ['slides' => $slides, 'leiloes' => $leiloes, 'input' => $request->all(), 'request' => $request ]);
    }
    
}
