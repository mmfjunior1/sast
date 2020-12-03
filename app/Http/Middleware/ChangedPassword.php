<?php

namespace App\Http\Middleware;
use Auth;
use Closure;

class ChangedPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public $attributes;
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if ($user && (int) $user->changed_password != 1) {
            return redirect('/meus-dados?changepassword=1')->with('success', 'Você precisa redefinir sua senha.');
        }
        return $next($request);
    }
}
