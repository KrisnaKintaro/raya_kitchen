<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use App\Models\CompanyProfile;
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
        Model::preventLazyLoading(! app()->isProduction());

        // Membagikan data company profile ke semua view yang butuh
        View::composer('*', function ($view) {
            $view->with('globalCompanyProfile', CompanyProfile::first());
        });
    }
}
