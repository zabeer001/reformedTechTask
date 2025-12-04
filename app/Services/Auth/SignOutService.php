<?php

namespace App\Services\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class SignOutService
{
    /**
     * Invalidate the current JWT session.
     *
     * @return string
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function __invoke(): string
    {
        try {
            Auth::guard('api')->logout();
        } catch (JWTException $exception) {
            report($exception);

            throw new AuthenticationException('Unable to sign out at this time.');
        }

        return 'Signed out successfully.';
    }
}
