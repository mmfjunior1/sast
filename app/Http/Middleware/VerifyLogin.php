<?php

namespace App\Http\Middleware;
use Auth;
use Closure;

class VerifyLogin
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
        if (!isset($user->codigo)) {
            return redirect()->back()->with('success', 'Para se habilitar neste leilão você precisa fazer seu login.');
        }
        return $next($request);
    }
}
