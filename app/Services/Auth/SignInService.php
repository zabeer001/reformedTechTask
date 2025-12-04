<?php

namespace App\Services\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Exceptions\JWTException;

class SignInService
{
    
    public function __invoke(array $credentials): array
    {
        try {
            $token = Auth::guard('api')->attempt($credentials);
        } catch (JWTException $exception) {
            report($exception);
            throw new AuthenticationException('Unable to create authentication token.');
        }

        if (! $token) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $ttl = (int) config('jwt.ttl', 60);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $ttl * 60,
            'user' => Auth::guard('api')->user(),
        ];
    }
}
