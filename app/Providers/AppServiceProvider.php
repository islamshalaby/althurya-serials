<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
// use Illuminate\Support\Facades\Auth;
use App\Category;
use App\Serial;
use App\SubCategory;
use App\SubFourCategory;
use App\SubTwoCategory;
use App\SubThreeCategory;
use App\SubFiveCategory;
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
        
    }
}
