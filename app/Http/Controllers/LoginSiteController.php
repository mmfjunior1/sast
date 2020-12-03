<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\UserSite;
use Illuminate\Support\Facades\Mail;
use App\Mail\RedefineSenha;
use Auth;

class LoginSiteController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->email;
        $senha = md5($request->senha);
        $user = UserSite::with('docPessoal')->where('email', '=', $email)->first();
        if (!$user) {
            return redirect()->back()->withErrors(['msg' => 'Email e/ou senha não foram encontrados ou email não está ativo no sistema!']);
        }
        if ($user && (int) $user->changed_password != 1) {
            if ($user && $senha == $user->senha) {
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
                Auth::login($user, true);
                return redirect()->back();
            }
        }
        
        $credentials = $request->only('email', 'senha');
        
        if (\Hash::check($credentials['senha'], $user->senha)) {
            Auth::login($user, true);
            return redirect()->back();
        }
        
        return redirect()->back()->withErrors(['msg' => 'Email e/ou senha não foram encontrados ou email não está ativo no sistema!']);
    }

    public function logout() {
        Auth::logout();
        return redirect('/');
    }

    public function esqueciMinhaSenha(Request $request)
	{
        $input = $request->all();
        $email = $input['email'];
        $senha = md5(time() . '#$Alfa==+§');
        $senha = substr($senha, 3, 7);
        
        $user = UserSite::where('email', $email)->first();
        if (!$user){
            return redirect()->back()->withErrors(['msg' => 'O email informado não foi encontrado ou não está ativo!']);
        }
        $user->senha = \Hash::make($senha);
        $user->changed_password = 0;
        $user->save();
        Mail::to($user->email)
        ->queue(new RedefineSenha($user, $senha, 'Nova senha de acesso'));
        
        return redirect()->back()->withErrors(['msg' => 'Sua nova senha será enviada para seu email, acesse-o e clique no link de ativação para atualizar a senha.']);
	}
}