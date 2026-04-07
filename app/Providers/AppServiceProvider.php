<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') != 'local') {
            // Note: BE CAREFUL!!!!!
            error_reporting(0);
        }

        Schema::defaultStringLength(191);

        Request::macro('getIdentifier', function () {
            $identifier = request()->input("identifier");
            return  $identifier ?? request()->input("username");
        });
    }
}
