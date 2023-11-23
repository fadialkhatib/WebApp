<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\checkin;
use App\Models\Queue;
use App\Models\user;

class book extends Controller
{
    public function checkin(Request $req){

        $token=json_decode(base64_decode($request->header('token')));

        $user=user::where('id',$token->user_id)->first();

        $Q=Queue::where('file_id',$req->file_id)->first();

        if(!$Q)     return response()->json(["message"=>"Forbidden"],405);

        else{
            if($Q->user_id == $user->id){
                $checknew=new checkin();
                $checkin->token_id=$token->id;
                $checkin->file_id=$req->file_id;
                $checkin->save();
                $Q->delete();
                $checkin->save();
                return redirect('api/download');
            }
            else{
                $Q->delete();
                return response()->json(["message"=>"This file isn't currerntly available"], 404);
            }
        }
    }
    public function createFile(Request $request){
        ////creating a new file
        /*
            required:
                Folder Name if exist or creating a new folder if not via redirect
                new File Name
                the new file must pelong to a specific user-id
                

        */
    }
}
