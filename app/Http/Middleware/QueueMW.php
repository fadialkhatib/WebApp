<?php

namespace App\Http\Middleware;

use App\Models\upload;
use Closure;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ActiveToken;
use App\Models\file;
use App\Models\Queue;
use App\Models\checkin;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

//App\Http\Middleware\QueueMW

class QueueMW
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token=json_decode(base64_decode($request->header('token')));
        $rules=array
        (
            "file_id"=> "required"
        );

        $validator=Validator::make($request->all(),$rules);

        if ($validator->fails())    return response()->json(['message'=>$validator->errors()],400);

        $file=upload::where('id',$request->file_id)->first();

        if(!$file)     return response()->json(["message"=>"wrong file_id"], 404);

        $checkin=checkin::where('file_id',$file->id)->first();
        $checkinwait=Queue::where('file_id',$file->id)->first();
        if($checkin || $checkinwait)    return response()->json(["message" => "File Not available"], 404);

        $Q=new Queue();
        $Q->user_id=$token->user_id;
        $Q->file_id=$file->id;
        $Q->save();
        return $next($request);
    }
}
