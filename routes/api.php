<?php

use App\Http\Middleware\QueueMW;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\book;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LoginController;
use App\Http\Middleware\AuthMiddleWare;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

     //////Login And Regesteer Methods ////
Route::post('user/create',[LoginController::class,'Create']);
Route::post('user/login',[LoginController::class,'login']);

    /////Login Required for this methods //////
    
    Route::middleware(AuthMiddleWare::class)->group(function () {
        
        Route::get('user/logout',[LoginController::class,'Logout']);
        
        //group prefix
        
        Route::post('group/create',[GroupController::class,'create_group']);
        Route::post('group/update',[GroupController::class,'update_group']);
        Route::post('group/delete',[GroupController::class,'delete_group']);
        Route::post('group/belong',[GroupController::class,'belong_to_group']);
        Route::post('group/leave',[GroupController::class,'leave_group']);
        Route::post('group/remove',[GroupController::class, 'knockout_from_group']);
        Route::get('group/mygroups',[GroupController::class, 'mygroups']);
        
        //File prefix
    Route::post('file/create',[book::class,'createFile']);
    Route::post('file/get',[book::class,'getFile']);
    Route::post('file/check',[book::class,'checkin'])->Middleware(QueueMW::class);
    Route::get('file/myfiles',[book::class,'myfiles']);
    Route::post('file/editfile',[book::class,'editFile']);
    Route::post('file/replace',[book::class,'replaceFile']);
    Route::post('file/deletefile',[book::class,'delete_file']);
    



});


/////Login and Check in required for these methods /////
Route::middleware(['AuthMidleWare', 'CheckInCheck'])->group(function () {
    Route::post('file/update',[book::class,'replace']);

});


