<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class book extends Controller
{
    public function checkin(Request $req){
        $token=json_decode(base64_decode($request->header('token')));
        $user=user::where('id',$token->user_id)->first();
        $Q=Queue::where('file_id',$req->file_id)->first();
        if($Q->user_id == $user->id){

            //check in and pop
        }
        else{
            //return message and pop the Q
        }
    }
}
