<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\ActiveToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    // login for manager & user
    function login(Request $req)
{
    $request=$req;
        $user=User::where('email',$request->email)->first();
        if(!$user)
        {
            return response()->json(['message'=>"you have to signup first"], 401);
        }
        if (!Hash::check($request['password'], $user->password))
            {
                 return response()->json(['message'=>"wrong password"], 401);
            }
        else
            $data=[
                "user_id" => $user->id,
                'name'=>$user->name,
                'rule'=>'user',
                'email'=>$user->email];
            if ($user->email == "manager@gmail.com")
            {
                $data['rule']="manager";
            }
            $tokenjson=json_encode($data);
            $token=base64_encode($tokenjson);
            $newtoken= New ActiveToken();
            $newtoken->user_id=$user->id;
            $newtoken->token=$token;
            $check=ActiveToken::where('token',$token)->first();
            if($check)
            {
                return response()->json(['message'=>'you already loged in'], 401);
            }
            $newtoken->save();
            return response()->json(['message'=>'succeeded','user_info' => $data,'token'=>$token],200);
}

    //signup for manager & user
    public function Create(Request $request)
    {
        $rules=array
        (
            "password"=> "required|min:8",
            "name"=>"required",
            "email"=>"required",
        );
        $validator=Validator::make($request->all(),$rules);
        if ($validator->fails())
        {
            return response()->json(['message'=>$validator->errors()],400);
        }

        $email_check=User::where('email',$request['email'])->first();
        if($email_check)
        {
            return response()->json(['message'=>'invalid email'],401);
        }
        $user=new User();
        $user->email=$request->email;
        $user->Password=Hash::make($request->password);
        $user->name=$request->name;

        $user->save();
        if($request->email == 'manager@gmail.com')
        {
            return response()->json(['message'=>'manager created successfully'],200);

        }
        return response()->json(['message'=>'user added successfully'],200);
    }
    //logout for manager & user
    public function Logout(Request $request)
    {
        $token=ActiveToken::where('token',$request->header('token'))->first();
        $token->delete();
        return response()->json(['message'=>'Logged out'], 200);
    }
}


