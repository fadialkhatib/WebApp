<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ChechIn;

class CheckInCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token=json_decode(base64_decode($request->header('token')));
        $checkin=CheckIn::where('file_id',$request->file_id)->where('user_id',$token->user_id)->first();
        if(!$checkin)       return response()->json(["message"=>"you are not allowed to edit this file right now"], 405);
        return $next($request);
    }
}
