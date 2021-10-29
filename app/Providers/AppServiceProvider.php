<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
	    Schema::defaultStringLength(191);

	    Validator::extend('custom_mimes', function ($attribute, $value, $parameters, $validator) {
		    return in_array( $value->getClientOriginalExtension(), $parameters );
	    });
	    Validator::replacer('custom_mimes', function ($message, $attribute, $rule, $extensions) {
		    return str_replace([':values'], [join(", ", $extensions)], $message);
	    });



	    View::composer('*', function($view) {
		    $user = Auth::user();
		    View::share(['user' => $user, 'template' => $view->getName(), 'templateName' => $view->getName()]);
	    });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
