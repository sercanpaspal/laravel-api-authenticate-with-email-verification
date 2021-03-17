<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiEmailVerificationRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        event(new Registered($user));

        return [
            'user' => UserResource::make($user),
            'access_token' => auth()->attempt(request(['email', 'password'])),
        ];
    }

    public function login(LoginRequest $request)
    {
        if (!$token = auth()->attempt($request->validated())) {
            return response(['message' => 'Unauthorized Attempt'], 401);
        }else if(!auth()->user()->hasVerifiedEmail()){
            return response(['message' => 'Your email address is not verified.'], 403);
        }

        return response(['user' => UserResource::make(auth()->user()), 'access_token' => $token]);
    }

    public function verify(ApiEmailVerificationRequest $request)
    {
        User::find($request->id)->markEmailAsVerified();

        return response(['message' => 'Verified!']);
    }

    public function update(UserUpdateRequest $request)
    {
        return UserResource::make(tap(auth()->user())->update($request->validated()));
    }
}
