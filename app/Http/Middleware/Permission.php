<?php

namespace App\Http\Middleware;

use Illuminate\Http\Response;
use Closure;
use Auth;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$user_types)
    {
        if ( ! in_array(\Auth::user()->user_type, $user_types)) {
            if( ! $request->ajax()){
               return back()->with('error', 'Sorry, You dont have permission to perform this action !');
            }else{
                return new Response('<h5 class="text-center red">Sorry, You dont have permission to perform this action !</h5>');
            }
        }
        return $next($request);
    }
}
