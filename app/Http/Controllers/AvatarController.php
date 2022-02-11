<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;
class AvatarController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $filePath = Storage::disk('local')
                ->putFile('avatars', new File($request->file), 'public');
            $user->avatar = env('AWS_ENDPOINT').$filePath;
            $user->save();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }
        return new UserResource($user);
    }
}
