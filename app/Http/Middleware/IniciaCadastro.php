<?php

namespace App\Http\Middleware;
use Auth;
use Closure;

class IniciaCadastro
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
        if (!$user) {
            return $next($request);
        }
        return redirect('/');
    }
}
