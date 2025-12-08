<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Services\Auth\RefreshTokenService;
use App\Services\Auth\RegisterService;
use App\Services\Auth\SignInService;
use App\Services\Auth\SignOutService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
use Throwable;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('auth:api')->only(['refresh', 'signout']);
    }

    //sign in 

    /**
     * @OA\Post(
     *     path="/api/auth/signin",
     *     tags={"Auth"},
     *     summary="Authenticate a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string", format="email")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function signin(Request $request, SignInService $signInService)
    {
        
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            $payload = $signInService($credentials);

            return $this->successResponse($payload, 'Signed in successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to sign in.', 401);
        }
    }


    //refresh 

    /**
     * @OA\Post(
     *     path="/api/auth/refresh",
     *     tags={"Auth"},
     *     summary="Refresh an existing JWT",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Refreshed token issued",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function refresh(RefreshTokenService $refreshTokenService)
    {
        try {
            $payload = $refreshTokenService();

            return $this->successResponse($payload, 'Token refreshed successfully.');
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to refresh token.', 401);
        }
    }

    //signout
    /**
     * @OA\Post(
     *     path="/api/auth/signout",
     *     tags={"Auth"},
     *     summary="Invalidate the current JWT",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Signed out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function signout(SignOutService $signOutService)
    {
        try {
            $message = $signOutService();

            return $this->successResponse(null, $message);
        } catch (Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to sign out.', 500);
        }
    }
}
