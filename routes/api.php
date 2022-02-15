<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Auth\TokenController;

use App\Http\Resources\UserBasicResource;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/sanctum/token/login', TokenController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return new UserBasicResource($request->user());
    });

    Route::get('/user/{user}', [UserController::class, 'show']);
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/user/avatar', AvatarController::class);

    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages', [MessageController::class, 'index']);

    Route::post('/sanctum/token/logout', function (Request $request) {
        $request->user()->tokens()->delete();
    });
});
