<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Auth;

class AdminIndexController extends Controller
{
    public function index()
    {
        $user = Auth::guard('admin')->user();
        
        return view('admin.index');
    }
}
