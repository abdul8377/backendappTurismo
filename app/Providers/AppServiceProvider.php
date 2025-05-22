<?php

namespace App\Providers;
use App\Models\MunicipalidadDescripcion;
use Illuminate\Support\Facades\View;


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
         View::composer('*', function ($view) {
        $municipalidad = MunicipalidadDescripcion::with('images')->first();
        $view->with('municipalidad', $municipalidad);
    });
    }
}
