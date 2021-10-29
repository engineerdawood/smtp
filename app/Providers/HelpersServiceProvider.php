<?php

namespace App\Providers;

use App\Custom\Helpers\CustomForm;
use App\Custom\Helpers\CustomHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class HelpersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('customhelper', function(){
            return new CustomHelper;
        });
        App::bind('customform', function(){
            return new CustomForm;
        });
    }
}
