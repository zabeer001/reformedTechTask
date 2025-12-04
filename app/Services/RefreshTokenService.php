<?php

namespace App\Services;

class RefreshTokenService
{
    public function __invoke(): string
    {
        return 'refresh route hit';
    }
}
