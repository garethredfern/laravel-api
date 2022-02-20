<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return App\Http\Resources\UserResource
     */
    public function index()
    {
        if (Auth::user()->isAdmin()) {
            return UserResource::collection(User::paginate(10));
        }
        return  response()->json(["message" => "Forbidden"], 403);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return App\Http\Resources\UserResource
     */
    public function show(User $user)
    {
        if (Auth::id() === $user->id || Auth::user()->isAdmin()) {
            return new UserResource($user);
        }
        return  response()->json(["message" => "Forbidden"], 403);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Http\Requests\UserRequest  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, User $user)
    {
        $user->update($request->all());
        return response()->json(["message" => "User updated"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
