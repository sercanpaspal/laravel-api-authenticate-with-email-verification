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
        event(new Registered(User::create($request->validated())));

        return response(['message' => 'Account created.'], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!$token = auth()->attempt($request->validated())) {
            return response(['message' => 'Unauthenticated.'], 401);
        }else if(!auth()->user()->hasVerifiedEmail()){
            return response(['message' => 'Your email address is not verified.'], 403);
        }

        return response(['user' => UserResource::make(auth()->user()), 'access_token' => $token]);
    }

    public function verify(ApiEmailVerificationRequest $request)
    {
        User::find($request->id)->markEmailAsVerified();

        return response(['message' => 'Account verified.']);
    }

    public function update(UserUpdateRequest $request)
    {
        return UserResource::make(tap(auth()->user())->update($request->validated()));
    }
}
