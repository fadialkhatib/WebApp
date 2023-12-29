<?php

namespace App\Http\Controllers;


use App\Models\belongtogroup;
use App\Models\group;
use App\Models\User;
use Exception;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class GroupController extends Controller
{
    ####### Create Group #######

    public function create_group(Request $request): JsonResponse
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

        }catch(Exception $e){
            return response()->json(['message'=>$e->getMessage()],401);
    }
    return response()->json(['Group created successfully!',$create,$belong]);
}

######## Update Group ######

public function update_group(Request $request): JsonResponse
{
    try{
        //توكن المعدل

        $user=User::where('id', json_decode(base64_decode($request->header('token')))->user_id)->value('id');

        //group id

        if (!empty($request->group_id)) {
            $group_id = group::where('id',$request->group_id)->value('id');
        }
        //echo $group_id;
        //للتحقق ان الذي سوف يعدل موجود في هذا الغروب
        if (!empty($group_id)) {
            $check = belongtogroup::where('user_id',$user)->where('group_id',$group_id)->value('id');
            if(!$check){
                return response()->json(['message'=>'you are not member on this group!'],401);
            }
        }

        //كود التعديل
        if (!empty($group_id)) {
            if (!empty($request->name)) {
                group::where('id',$group_id)->update([
                    'name'=> $request->name,
                ]);
                return response()->json(['message'=>'Group name updated successfully!']);
            }
        }
    }catch(Exception $e)
    {
        return response()->json(['message'=>$e->getMessage()],401);
    }
    return response()->json(["message" => "Some thing went wrong"], 405);
}


######## Delete Group ##########

public function delete_group(Request $request): JsonResponse
{
        //توكن الحاذف
        $token=json_decode(base64_decode($request->header('token')));
        $user=User::where('id',$token->user_id)->value('id');
        //group id
    if (!empty($request->group_id)) {
        $group_id = group::where('id',$request->group_id)->value('id');
    }
        //للتحقق من وجود الحاذف ضمن الغروب
    if (!empty($group_id)) {
        $check = belongtogroup::where('user_id',$user)
            ->where('group_id',$group_id)->value('id');
        if(!$check){
            return response()->json(['message'=>'you are not member on this group!'],401);
        }
        group::where('id',$group_id)->delete();
        return response()->json(['message'=>'Group name deleted successfully!']);

    }
        return response()->json(["message" => "Some thing went wrong"],404);
        //كود الحذف
    }


##########  Add member to group  ###########

public function belong_to_group(Request $request): JsonResponse
{
    //توكن عضو الغروب
    $token=json_decode(base64_decode($request->header('token')));
    $user=User::where('id',$token->user_id)->value('id');
    //group id
    if (!empty($request->group_id)) {
        $group_id = group::where('id',$request->group_id)->value('id');

        if(!$group_id)
        {
            return response()->json(['message'=>'this group not exist!'],401);
        }
        $check = belongtogroup::where('user_id',$user)
            ->where('group_id',$group_id)->first();
        if(!$check){
            return response()->json(['message'=>'You are not belongs to this group!'],401);
        }
        //اي دي اليوزر الذي سوف ينضم
        if (!empty($request->new_user)) {
            $new_user = User::where('id',$request->new_user)->value('id');

            $check_new_user = belongtogroup::where('user_id',$new_user)
                ->where('group_id',$group_id)->value('user_id') ;
            if($check_new_user){
                return response()->json(['message'=>'this user is already in the group']);
            }
        }
        //التحقق انه ليس موجود مسبقا

    }
    //التحقق من وجود الغروب

    //التحقق من صاحب التوكن انه عضو


    if (!empty($new_user)) {
        if (!empty($group_id)) {
            belongtogroup::create([
                'user_id'  =>$new_user,
                'group_id' => $group_id
            ]);
        }
    }
    return response()->json(['message'=>'New user have joined this group']);
}

#######  Leave the group from user  #########

public function leave_group(Request $request): JsonResponse
{
    //توكن الشخص المغادر
    $token=json_decode(base64_decode($request->header('token')));
    $user=User::where('id',$token->user_id)->value('id');
    //group id
    if (!empty($request->group_id)) {
        $group_id = group::where('id',$request->group_id)->value('id');

        if(!$group_id)
        {
            return response()->json(['message'=>'this group not exist!'],401);
        }
        $check = belongtogroup::where('user_id',$user)->where('group_id',$group_id)->first();
        if($check){
            $leave = belongtogroup::where('user_id',$user)
                ->where('group_id',$group_id)->first();
            $leave->delete();
            return response()->json(['message'=>'yor have left this group']);
        }
        else{
            return response()->json(['message'=>'you left the group already'],401);
        }
    }
    //التحقق من وجود الغروب

    //التحقق ان الشخص المغادر موجود ضمن الغروب
    return response()->json(["message" => "Some thing went wrong"],404);

}

######## Member of group can remove another member from this group #########

public function knockout_from_group(Request $request): JsonResponse
{
    //توكن عضو الغروب
    $token=json_decode(base64_decode($request->header('token')));
    $user=User::where("id",$token->user_id)->value('id');
    //group id
    if (!empty($request->group_id)) {
        $group_id = group::where('id',$request->group_id)->value('id');
        if(!$group_id)
        {
            return response()->json(['message'=>'this group not exist!'],401);
        }
        //التحقق ان العضو هو عضو
        $check = belongtogroup::where('user_id',$user)->where('group_id',$group_id)->first();
        if(!$check){
            return response()->json(['message'=>'you are not in group !'],401);
        }
//اسم اليوزر المكحوش
        if (!empty($request->user2)) {
            $user2 = User::where('id',$request->user2)->value('id');

//التحقق انو الي بنا نكحشو موجود اساسا منشان نكحشو
            $user_to_knockout = belongtogroup::where('user_id',$user2)->where('group_id',$group_id)->value('user_id');
            if(!$user_to_knockout)
            {
                return response()->json(['message'=>'this user is not member in this group!'],401);
            }else{
                belongtogroup::where('user_id',$user2)->where('group_id',$group_id)->delete();
                return response()->json(['message'=>'user deleted successfully !']);
            }
        }
    }
    return response()->json(["message" => "Some Thing went wrong"],404);
    //التحقق من وجود الغروب

}

    public function mygroups(Request $request)
    {
        try{
        $token=json_decode(base64_decode($request->header('token')));
        $user=User::where("id",$token->user_id)->value('id');
        $mygroups_id = belongtogroup::where('user_id',$user)->first();
        if(!$mygroups_id)
        {
            return response()->json(['message'=>'you are not joined to any group'],401);
        }
        foreach ($mygroups_id as $group)
        {
            $id = $group->group_id;
            $mygroup = group::where('id',$id)->value('name');
        }
    }catch(\Exception $e){
        return response()->json(['message'=>$e->getMessage()],401);

    }
        return response()->json(['mygroups'=>$mygroup]);
    }
}
