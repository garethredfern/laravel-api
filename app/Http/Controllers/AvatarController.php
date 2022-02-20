<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Http\Requests\UserAvatarRequest;
class AvatarController extends Controller
{
    /**
     * Update the user's avatar.
     *
     * @param  \Http\Requests\UserRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(UserAvatarRequest $request, User $user)
    {
        try {
            $path = $request->file('file')->storePublicly('public/avatars');
            $user->avatar = $path;
            $user->save();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
        return response()->json(["message" => "Avatar updated"], 200);
    }
}
