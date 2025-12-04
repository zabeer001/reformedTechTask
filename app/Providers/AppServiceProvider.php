<?php

namespace App\Providers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Authenticate::redirectUsing(fn () => null);
        AuthenticationException::redirectUsing(fn () => null);

        File::ensureDirectoryExists(storage_path('app/api-docs'));

        config([
            'l5-swagger.documentations.default.api.title' => env('APP_NAME', 'Laravel').' API Docs',
            'l5-swagger.defaults.securityDefinitions.securitySchemes.bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
                'description' => 'Add Authorization header in the format: Bearer {token}',
            ],
            'l5-swagger.defaults.securityDefinitions.security' => [
                [
                    'bearerAuth' => [],
                ],
            ],
            'l5-swagger.defaults.paths.docs' => storage_path('app/api-docs'),
        ]);
    }
}
