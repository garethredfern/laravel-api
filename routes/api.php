<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\AvatarController;
use App\Http\Resources\UserResource;
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
  Route::get('/users/auth', function () {
    return new UserResource(Auth::user());
  });

  Route::get('/users/{id}', function ($id) {
    return new UserResource(User::findOrFail($id));
  });

  Route::get('/users', function () {
    return UserResource::collection(User::paginate());
  });

  Route::post('/users/auth/avatar', [AvatarController::class, 'store']);
});
