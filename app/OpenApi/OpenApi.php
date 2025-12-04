<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Reformed Tech API",
 *     version="1.0.0",
 *     description="API documentation for the Reformed Tech authentication endpoints."
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Include your JWT access token prefixed with Bearer"
 * )
 */
class OpenApi
{
}
