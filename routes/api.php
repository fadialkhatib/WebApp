<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controller\book;
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
Route::middleware(['AuthMidleWare'])->group(function () {
    Route::post('Check',[book::class,'checkin'])->Middleware(Queue::class);

});

        /////Login an Check in required for this methods /////
Route::middleware(['AuthMidleWare', 'CheckInCheck'])->group(function () {

});

