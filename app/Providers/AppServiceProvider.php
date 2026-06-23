<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

use Illuminate\Support\Facades\URL;


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
     $this->configureDefaults();
    Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
        $openApi->secure(
            SecurityScheme::http('bearer', 'JWT')
        );
    });

    if (app()->environment('production')) {
        URL::forceScheme('https');
    }

    if (!file_exists(public_path('storage'))) {
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
        } catch (\Exception $e) {
            // Fail silently
        }
    }
}


    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

