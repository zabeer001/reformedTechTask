<?php

namespace App\Services\Auth;

use Illuminate\Auth\AuthenticationException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class RefreshTokenService
{
   
    public function __invoke(): array
    {
        try {
            $token = JWTAuth::parseToken()->refresh();
        } catch (JWTException $exception) {
            report($exception);

            throw new AuthenticationException('Unable to refresh token.');
        }

        $ttl = (int) config('jwt.ttl', 60);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60,
        ];
    }
}
