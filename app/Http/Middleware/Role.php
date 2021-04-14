<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Responses\ResponseController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, String $role)
    {
        $loginRole = Auth::user()->user_role;
        if ($loginRole == $role) {
            return $next($request);
        }
        return ResponseController::sendError('This page is not accessible', 403);
    }
}
