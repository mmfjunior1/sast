<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Lotes;
use App\Lances;
use App\LancesParcelados;
use Auth;

class InstitucionalController extends Controller
{
    public function index(Request $request)
    {
        $titlePage = explode('/', $request->getUri());
        $link = $titlePage[3] == '' ? 'quem-somos': $titlePage[3];
        $text = getInstitutionalText($link);
        $titlePage = strtoupper(str_replace('-', ' ', $link));
        return view('site.institucional.institucional', ['title' => $titlePage, 'text' => $text, 'link' => $link]);
    }
}