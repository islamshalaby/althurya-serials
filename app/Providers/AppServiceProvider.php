<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Serial;
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
        Serial::where('deleted', 0)->where('sold', 0)->get()
        ->map(function ($row) {
            $validTo = Carbon::parse($row->valid_to);
            if ($validTo->isPast()) {
                $row->deleted = 1;
                $row->save();
            }
        });
    }
}
