<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email
    {email : Email address where to send test email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will be used to test email';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
	    Mail::send( 'emails.test_mail', [], function ( $message ) {
		    $message->to( $this->argument('email') )->subject( 'Testing email' );
	    } );
    }
}
