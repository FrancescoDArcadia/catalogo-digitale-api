<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request) {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'=> $user,
            'token'=> $token,
        ], 201);
    }

    public function login(LoginUserRequest $request) {
        $user = User::where('email', $request->validated('email'))->first();
        if(! $user || ! Auth::validate(
            [
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
            ]
        )) {
            throw ValidationException::withMessages([
                'email' => ['le credenziali fornite non sono corrette'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function logoutAll(Request $request) {
        $request->user()->tokens()->delete();
        return response()->noContent();
    }

    public function me(Request $request){
        return UserResource::make($request->user());
    }

}
