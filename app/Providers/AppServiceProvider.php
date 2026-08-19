<?php

namespace App\Providers;

use App\Models\Parameter;
use App\Observers\ParameterObserver;
use Illuminate\Support\Facades\URL;
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
        Parameter::observe(ParameterObserver::class);

        if ($this->app->environment('production') || env('FORCE_HTTPS', false) || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }
    }
}
