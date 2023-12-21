<?php

namespace App\Http\Middleware;

use App\Models\Login;
use App\Models\Token;
use Closure;
use Illuminate\Http\Request;

class AuthUser
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
        if ($userToken && ($userToken->login->active == 1) && $userToken->login->role == 'general') {
            Login::where('id', $userToken->login->id)->update(['last_request' => date('Y-m-d H:i:s')]);   // Update app user's Last Request Time
            return $next($request);
        }
        return response()->json(["msg" => "Unauthorized"], 401);
    }
}
