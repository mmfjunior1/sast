<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('admin.login.login');
    }
    public function login(Request $request)
    {
        $recap = 'g-recaptcha-response';
        $resposta = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Leo7-sUAAAAABqIHy9uY8klzyRNYxqCpzSORGYs&response=".$request->$recap."&remoteip=".$_SERVER['REMOTE_ADDR']);
        $resposta = json_decode($resposta);
        // if(gethostname() == 'mario-Inspiron-5567') {
        //     $resposta = new stdClass();
        //     $resposta->success = true;
        // }
        if (!$resposta->success) {
            return redirect()->back()->with('error_login', 'Você deve marcar o recaptcha');
        }
        $email = $request->email;
        $senha = md5($request->senha);
        $user = User::where('email', '=', $email)->first();
        if ($user && $senha == $user->senha) {
            Auth::guard('admin')->login($user, true);
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            return redirect('/cadastro-leiloes');
        }
        return redirect()->back()->with('error_login', 'Email e/ou senha não foram encontrados ou email não está ativo no sistema!');
    }

    public function logout() {
        Auth::guard('admin')->logout();
        return redirect('signin');
    }
}