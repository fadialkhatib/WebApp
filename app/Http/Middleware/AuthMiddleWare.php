<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActiveToken;
use Symfony\Component\HttpFoundation\Response;

//App\Http\Middleware\AuthMiddleWare
class AuthMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$request->hasHeader('token') || $request->header('token') =="")      return response()->json(["message" => "Missed Token"], 401);
        $token=json_decode(base64_decode($request->header('token')));
        $check=ActiveToken::where('token',$request->header('token'))->first();
        if(!$check) return response()->json(["message" => "InValid Token"], 401);

        return $next($request);
    }
}
