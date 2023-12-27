<?php

namespace App\Http\Controllers;
use App\Models\ActiveToken;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Models\checkin;
use App\Models\Folder;
use App\Models\belongtogroup;
use App\Models\File as ModelsFile;
use App\Models\upload;
use App\Models\Queue;
use App\Models\group;

use App\Models\user;

class book extends Controller
{
    public function checkin(Request $req){

        $token=json_decode(base64_decode($req->header('token')));
        $tokendb=ActiveToken::where('token',$req->header('token'))->first();
        $user=user::where('id',$token->user_id)->first();
        $Q=Queue::where('file_id',$req->file_id)->first();

        if(!$Q)     return response()->json(["message"=>"Not Allowed"],405);

        else{
            
            if($Q->User_id == $user->id){
                $checknew=new checkin();
                $checknew->token_id=$tokendb->id;
                $checknew->file_id=$req->file_id;
                $checknew->save();
                $Q->delete();
                return response()->json(["messsage" => "done"],200);
            }
            else{
                $Q->delete();
                return response()->json(["message"=>"This file isn't currerntly 00available"], 404);
            }
        }
    }
    public function createFile(Request $request){
       
        $token=json_decode(base64_decode($request->header('token')));

        $folder=Folder::where('Folder',$request->Folder_name)->first();
        if(!$folder){
            $folder=new Folder();
            $folder->Folder=$request->Folder_name;
            //group
            $folder->save();
        }
        //تحقق اذا كان هناك فايل بنفس الاسم داخل الفولدر
        $file=upload::where('name',$request->fileName)->where('Folder_id',$folder->id)->first();

        if($file)       return response()->json(["message"=>"Invalid File name"], 400);
        //هل الغروب موجود ام لا
        $group=group::where('id',$request->group_id)->first();

        if(!$group)     return response()->json(["message"=>"wrong Group ID"], 400);
        //تحقق اذا كان اليوزر ضمن الغروب
        $belong=belongtogroup::where('group_id',$group->id)->where('user_id',$token->user_id)->first();

        if(!$belong)      return response()->json(["message" => "you are not a member of this group"], 401);

//        try and catch
        $file=new Upload();
        $file->name=$request->fileName;
        $file->Folder_id=$folder->id;
        $file->owner_id=$token->user_id;
        $file->group_id=$request->group_id;
        $file->File_Path='uploads\\'.$request->fileName;
        Storage::put('uploads\\'.$request->fileName.".txt", "V 1.0 \n");
        $file->save();

        return response()->json(
                        ["message"=>"Done",
                         "path" => $file->File_Path
                        ],
                          200);

    }

    public function getFile(Request $request){
        $token=json_decode(base64_decode($request->header('token')));

        $folder=Folder::where('Folder',$request->Folder_name)->first();
        if(!$folder){
            return response()->json(["message" => "Invalid Folder Name"], 404);
        }

        // تحقق

        $file=upload::where('name',$request->fileName)->where('Folder_id',$folder->id)->first();

        if(!$file)       return response()->json(["message"=>"In Vlaid"], 404);
        //Checking if the Group dose Exist

        $group=group::where('id',$file->group_id)->first();

        if(!$group)     return response()->json(["message"=>"Invalid Group ID"], 400);
        //Checking if the user is a member of this group
        $belongto=belongtogroup::where('group_id',$group->id)->where('user_id',$token->user_id)->first();

        if(!$belongto)      return response()->json(["message" => "you are not a member of this group"], 401);

        $filepath='uploads\\'.$request->fileName.".txt";
        $file=Storage::get($filepath);
        Storage::move($filepath, 'public/');

        return Response::make($file, 200);
        }


    
    public function myfiles(Request $request)
    {
        try{
        $token = json_decode(base64_decode($request->header('token')));
        $user=User::where('id',$token->user_id)->value('id');
        $files = upload::where('owner_id',$user)->get();
        foreach($files as $file)
        {
            $folder[] = Folder::where('id',$file->Folder_id)->value('Folder');
            $file_path []= $file->File_Path;
            $file_content = file_get_contents(storage_path('app/'.$file->File_Path.'.txt'));
            $show[] = $file_content;

        }
    }catch(\Exception $e)
    {
    return response()->json(['message'=>$e->getMessage()]);
    }
        return response()->json(['folders'=>$folder,'files'=>$file_path,'details'=>$show],200);
    }
    
    
    public function delete_file(Request $request)
    {
        try{
        $token = json_decode(base64_decode($request->header('token')));
        $user=User::where('id',$token->user_id)->value('id');
        $belongs = belongtogroup::where('user_id',$user)->first();
        if(!$belongs)
        {
            return response()->json(['message'=>'you are not joined to any group!'],401);

        }
        $check = upload::where('owner_id',$user)->where('id',$request->id)->first();
        if(!$check)
        {
            return response()->json(['message'=>'you dont have any files or this file not exist'],401);
        }
        $delete = $check->delete();
    }catch(\Exception $e)
    {
    return response()->json(['message'=>$e->getMessage()]);
    }
        return response()->json(['message'=>'this file deleted successfully!'],200);
    }
    

    public function editFile(Request $request)
{
    //تحقق من أن اليوزر يملك هذا الفايل
    try{
    $token = json_decode(base64_decode($request->header('token')));
    $user=User::where('id',$token->user_id)->value('id');
    $filePath = 'uploads\\'.$request->fileName.".txt"; // اسم الملف الذي تريد تعديله
    // قم بقراءة المحتوى الحالي للملف
    $check = upload::where('owner_id',$user)->where('name',$request->fileName)->first();
    if(!$check){
        return response()->json(['message'=>'this file not belong to you .. you cant edit it !'],500);
    }
    $currentContent = Storage::get($filePath);
     $backupPath = 'backup/' . $request->fileName . "_backup.txt";
     Storage::move($filePath, $backupPath);
    // قم بتعديل المحتوى كما تريد
    $newContent = "بيانات جديدة";

    // قم بكتابة المحتوى الجديد إلى الملف
    Storage::put($filePath, $newContent);
}catch(\Exception $e)
{
    return response()->json(['message'=>$e->getMessage()]);
}

    return response()->json(["message"=>"the content editing successfully!", $newContent]); 
}
public function replaceFile(Request $request)
{
    try{
    $token = json_decode(base64_decode($request->header('token')));
    $user=User::where('id',$token->user_id)->value('id');
    $filePath = 'uploads\\'.$request->fileName.".txt"; // اسم الملف الذي تريد تعديله
    // قم بقراءة المحتوى الحالي للملف
    $check = upload::where('owner_id',$user)->where('name',$request->fileName)->first();
    if(!$check){
        return response()->json(['message'=>'this file not belong to you .. you cant do any thing !'],500);
    }

    $filePath = 'uploads\\'.$request->fileName.".txt"; // اسم الملف الذي تم تعديله

    // استرجع المحتوى السابق من نسخة احتياطية
    $backupPath ='backup\\'.$request->fileName."_backup.txt" ;
    $backupContent=Storage::get($backupPath);
    // قم بكتابة المحتوى السابق إلى الملف لاستعادته
    Storage::put($filePath, $backupContent);
    }catch(\Exception $e)
    {
    return response()->json(['message'=>$e->getMessage()]);
    }
    return response()->json(["message"=>"the content restored successfully!",$backupContent]);
}
}
