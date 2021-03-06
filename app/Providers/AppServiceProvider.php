<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Serial;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

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
        // get permissions of current admin
        date_default_timezone_set('Asia/Kuwait');
        Artisan::call('serial:update');
    }
}
