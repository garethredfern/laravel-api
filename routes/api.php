<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\AvatarController;
use App\Models\User;

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

Route::post('/sanctum/token', TokenController::class);

Route::middleware(['auth:sanctum'])->group(function () {
  Route::get('/users/{user}', function (Request $request) {
    return $request->user();
  });

  Route::get('/users', function () {
    return User::all();
  });

  Route::get('/users/auth', function () {
    return auth()->user();
  });

  Route::post('/users/auth/avatar', [AvatarController::class, 'store']);
});
