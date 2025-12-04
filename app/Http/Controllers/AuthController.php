<?php

namespace App\Http\Controllers;

use App\Services\Auth\RefreshTokenService;
use App\Services\Auth\RegisterService;
use App\Services\Auth\SignInService;
use App\Services\Auth\SignOutService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function signin(Request $request, SignInService $signInService)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        return response()->json($signInService($credentials));
    }

    public function register(RegisterService $registerService)
    {
        return response()->json(['message' => $registerService()]);
    }

    public function refresh(RefreshTokenService $refreshTokenService)
    {
        return response()->json(['message' => $refreshTokenService()]);
    }

    public function signout(SignOutService $signOutService)
    {
        return response()->json(['message' => $signOutService()]);
    }
}
