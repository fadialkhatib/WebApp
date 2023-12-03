<?php

namespace App\Http\Controllers;

use App\Models\ActiveToken;
use App\Models\belongtogroup;
use App\Models\group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Exists;

class GroupController extends Controller
{
    ####### Create Group #######

    public function create_group(Request $request)
    {
        try{
        $token=json_decode(base64_decode($request->header('token')));
        $user=User::where('id',$token->user_id)->value('id');
        $validate =$request->validate([
            'name'=>'required|string'
        ]);
        //Create group
        $create = group::create(
            [
                'name'=>$validate['name']
            ]);
        //اضافة الشخص الذي أنشأ الغروب الى الغروب
        $belong = belongtogroup::create([
            'user_id'=>$user,
            'group_id'=>$create->id
        ]);
        
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],401);
    }
    return response()->json(['Group created successfully!',$create,$belong],200);
}

######## Update Group ######

public function update_group(Request $request)
{
    try{
        //توكن المعدل
        $token=json_decode(base64_decode($request->header('token')));
        $user=User::where('id',$token->user_id)->value('id');
        //group id
        $group_id = group::where('id',$request->group_id)->value('id');
        //echo $group_id;
        //للتحقق ان الذي سوف يعدل موجود في هذا الغروب
        $check = belongtogroup::where(['user_id'=>$user,'group_id'=>$group_id])->value('id');
        if(!$check){
            return response()->json(['message'=>'you are not member on this group!'],401);
        }
        //كود التعديل
        $update = group::where('id',$group_id)->update([
            'name'=> $request->name,
        ]);
    }catch(\Exception $e)
    {
        return response()->json(['message'=>$e->getMessage()],401);
    }
    return response()->json(['message'=>'Group name updatesd successfully!'],200);
}


######## Delete Group ##########

public function delete_group(Request $request)
{
        //توكن الحاذف
        $token=json_decode(base64_decode($request->header('token')));
        $user=User::where('id',$token->user_id)->value('id');
        //group id
        $group_id = group::where('id',$request->group_id)->value('id');
        //للتحقق من وجود الحاذف ضمن الغروب
        $check = belongtogroup::where(['user_id'=>$user,'group_id'=>$group_id])->value('id');
        if(!$check){
            return response()->json(['message'=>'you are not member on this group!'],401);
        }
        //كود الحذف
    group::where('id',$group_id)->delete();   
    return response()->json(['message'=>'Group name deleted successfully!'],200);
}


##########  Add member to group  ###########

public function belong_to_group(Request $request)
{
    //توكن عضو الغروب
    $token=json_decode(base64_decode($request->header('token')));
    $user=User::where('id',$token->user_id)->value('id');
    //group id
    $group_id = group::where('id',$request->group_id)->value('id');
    //التحقق من وجود الغروب
    if(!$group_id)
    {
        return response()->json(['message'=>'this group not exist!'],401);
    }
    //التحقق من صاحب التوكن انه عضو
    $check = belongtogroup::where(['user_id'=>$user,
    'group_id'=>$group_id])->first();
    if(!$check){
        return response()->json(['message'=>'You are not belongs to this group!'],401);
    }
    //اي دي اليوزر الذي سوف ينضم
    $new_user = User::where('id',$request->new_user)->value('id');
    //التحقق انه ليس موجود مسبقا
    $check_new_user = belongtogroup::where(['user_id'=>$new_user,'group_id'=>$group_id])->value('user_id') ;
    if($check_new_user){
        return response()->json(['message'=>'this user is already in the group']);
    }
    $belong = belongtogroup::create([
        'user_id'  =>$new_user,
        'group_id' => $group_id
    ]);
    return response()->json(['message'=>'New user have joined this group'],200);
}

#######  Leave the group from user  #########

public function leave_group(Request $request)
{
    //توكن الشخص المغادر
    $token=json_decode(base64_decode($request->header('token')));
    $user=User::where('id',$token->user_id)->value('id');
    //group id
    $group_id = group::where('id',$request->group_id)->value('id');
    //التحقق من وجود الغروب
    if(!$group_id)
    {
        return response()->json(['message'=>'this group not exist!'],401);
    }
    //التحقق ان الشخص المغادر موجود ضمن الغروب
    $check = belongtogroup::where(['user_id'=>$user,
    'group_id'=>$group_id])->first();
    if($check){
    $leave = belongtogroup::where(['user_id'=>$user,
                                    'group_id'=>$group_id])->delete([
        'user_id'  => $user,
        'group_id' => $group_id
    ]);
    return response()->json(['message'=>'yor have left this group'],200);
    }
    else{
        return response()->json(['message'=>'you left the group already'],401);
    }
}

######## Member of group can remove another member from this group #########

public function kickout_from_group(Request $request)
{
    //توكن عضو الغروب
    $token=json_decode(base64_decode($request->header('token')));
    $user=User::where('id',$token->user_id)->value('id');
    //group id
    $group_id = group::where('id',$request->group_id)->value('id');
    //التحقق من وجود الغروب
    if(!$group_id)
    {
        return response()->json(['message'=>'this group not exist!'],401);
    }
    //التحقق ان العضو هو عضو 
    $check = belongtogroup::where(['user_id'=>$user,
    'group_id'=>$group_id])->first();
    if(!$check){
        return response()->json(['message'=>'you are not in group !'],401);
}
//اسم اليوزر المكحوش
$user2 = User::where('id',$request->user2)->value('id');
//التحقق انو الي بنا نكحشو موجود اساسا منشان نكحشو
$user_to_kickout = belongtogroup::where(['user_id'=>$user2,'group_id'=>$group_id])->value('user_id');
if(!$user_to_kickout)
{
    return response()->json(['message'=>'this user is not member in this group!'],401);
}else{
    belongtogroup::where(['user_id'=>$user2,'group_id'=>$group_id])->delete();
    return response()->json(['message'=>'user deleted successfully !'], 200);
}
} 
}
