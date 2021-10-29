<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

	    if ( app()->environment( 'demo' ) ) {
		    Event::listen( [
			    'eloquent.creating*',
			    'eloquent.updating*',
			    'eloquent.saving*',
			    'eloquent.deleting*',
			    'eloquent.restoring*',
		    ], function ( $event, $eloq ) {
			    return false;
		    } );
	    }
    }
}
