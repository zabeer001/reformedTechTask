<?php

namespace App\Services\Auth;

class RefreshTokenService
{
    public function __invoke(): string
    {
        return 'refresh route hit';
    }
}
