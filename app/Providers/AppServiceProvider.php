<?php

namespace App\Providers;

use App\Models\Parameter;
use App\Observers\ParameterObserver;
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
    }
}
