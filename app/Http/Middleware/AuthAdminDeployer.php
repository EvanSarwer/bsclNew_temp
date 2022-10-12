<?php

namespace App\Http\Middleware;

use App\Models\Token;
use Closure;
use Illuminate\Http\Request;

class AuthAdminDeployer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        $userToken = Token::where('token', $token)->first();
        if ($userToken && ($userToken->login->role == 'admin' || $userToken->login->role == 'deployer')) {
            return $next($request);
        }
        return response()->json(["msg" => "Unauthorized"], 401);
    }
}