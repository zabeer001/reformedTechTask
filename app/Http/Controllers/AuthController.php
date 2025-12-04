<?php

namespace App\Http\Controllers;

use App\Services\RefreshTokenService;
use App\Services\RegisterService;
use App\Services\SignInService;
use App\Services\SignOutService;

class AuthController extends Controller
{
    public function signin(SignInService $signInService)
    {
        return response()->json(['message' => $signInService()]);
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
