<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UpdatePasswordController extends Controller
{
    /**
     * Validate and update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $currentPassword = $request->current_password;

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! isset($currentPassword) || ! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return response()->json(["message" => "Password updated"], 200);
    }
}
