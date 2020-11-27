<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;

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
        Schema::defaultStringLength(191);

        Blade::directive('getoption', function ($name, $default_value = '') {
            return "<?= (get_option($name, $default_value)); ?>";
        });

        Blade::if('usertype', function ($user_type) {
            return \Auth::user()->user_type == $user_type;
        });
    }
}
