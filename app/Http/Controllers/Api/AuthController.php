<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    
    private function refreshTokenTtlDays(): int {
        return (int) env('REFRESH_TOKEN_TTL_DAYS',30);
    }

    public function register(RegisterUserRequest $request) {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);

        return $this->respondWithTokens($user, 201);
        //$token = $user->createToken('api-token')->plainTextToken;

        /* return response()->json([
            'user'=> $user,
            'token'=> $token,
        ], 201); */
    }

    public function login(LoginUserRequest $request) {
        $user = User::where('email', $request->validated('email'))->first();
        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['le credenziali fornite non sono corrette.'],
            ]);
        } 

        return $this->respondWithTokens($user);

        /* $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]); */
    }

    public function refresh(Request $request) {
        $plainToken = $request->cookie('refresh_token');

        if (! $plainToken) {
            return response()->json(['message' => 'Refresh token mancante'], 401);
        }

        $hashed=hash('sha256', $plainToken);

        $record = RefreshToken::valid()->where('token_hash', $hashed)->first();
        if (! $record) {
            return response()->json(['message' => 'Refresh token non valido o scaduto'], 401);
        }

        $record->update(['revoked_at' => now()]);
        return $this->respondWithTokens($record->user);
    }

    public function logout(Request $request)
    {
        //request()->user()->currentAccessToken()->delete();
        //return response()->noContent();
        $plainToken = $request->cookie('refresh_token');
        if ($plainToken) {
            RefreshToken::where('token_hash', hash('sha256', $plainToken))
            ->update( ['revoked_at' => now()]);
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Throwable) {

        }

        return response()->noContent()->withCookie(cookie()->forget('refresh_token'));
    }

    /* public function logoutAll(Request $request) {
        $request->user()->tokens()->delete();
        return response()->noContent();
    } */

    public function me(Request $request){
        return UserResource::make($request->user());
    }

    private function respondWithTokens(User $user, int $status = 201) {
        $accessToken = auth('api-jwt')->login($user);
        $plainRefreshToken = Str::random(64);
        RefreshToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainRefreshToken),
            'expires_at' => now()->addDays($this->refreshTokenTtlDays()),
        ]);

        $refreshCookie = cookie(
            name: 'refresh_token',
            value: $plainRefreshToken,
            minutes: 60 * 24 * $this->refreshTokenTtlDays(),
            path: '/api',
            domain: null,
            secure: app()->isProduction(),
            httpOnly: true,
            sameSite: 'Lax',
        );
        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api-jwt')->factory()->getTTL()*60,
            'user' => UserResource::make($user),
        ], $status)->withCookie($refreshCookie);
    }

}
