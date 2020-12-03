<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\User;
use Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    //protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    // }

    public function index()
    {
        return view('admin.login.login');
    }

    public function login(Request $request)
    {
        $email = $request->email;
        $senha = md5($request->senha);
        $user = User::where('email', '=', $email)->first();
        
        if ($user && $senha == $user->senha) {
            
            Auth::guard('admin')->login($user, true);
            $user = Auth::guard('admin')->user();
            echo '<pre>';
            print_r($user);die;
            die("AAAAAAA");
            return redirect()->back();
        }
        return redirect('/')->withErrors(['msg' => 'Email e/ou senha não foram encontrados ou email não está ativo no sistema!']);
    }

    public function logout() {
        Auth::guard('admin')->logout();
        return redirect('/');
    }
}
