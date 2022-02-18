<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
class AvatarController extends Controller
{
    /**
     * Update the user's avatar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        try {
            $user = $request->user();
            $path = $request->file('file')->storePublicly('public/avatars');
            $user->avatar = $path;
            $user->save();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
        return response()->json(["message" => "Avatar updated"], 200);
    }
}
