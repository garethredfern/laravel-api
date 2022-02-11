<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
class AvatarController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $user = $request->user();
            $path = $request->file('avatar')->storePublicly('public/avatars');
            $user->avatar = $path;
            $user->save();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
        return new UserResource($user);
    }
}
