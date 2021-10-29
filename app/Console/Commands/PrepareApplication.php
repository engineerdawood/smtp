<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PrepareApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will prepare application by installing every required thing.';

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

	    $this->info( "Checking database connection" );
	    try {
	        $dbWorking = true;
		    DB::connection()->getPdo();
	    } catch (\Exception $e) {
		    $dbWorking = false;
	    }

	    if(!$dbWorking){
	    	$this->error("Could not connect to the database. Please check your configuration in " . base_path('.env') . " file.");
	    	$this->info("Stopping setup due to error.");
	    	return false;
	    } else {
		    $this->info("Hooray, Database connection is working fine.");
	    }

	    $this->info( "Starting installer" );

	    // Running required artisan commands
	    $this->info("Running required commands and creating tables.");
	    $commands = ['key:generate', 'migrate'];
	    foreach ( $commands as $cmd ) {
		    $this->call($cmd);
	    }

	    // Creating required folders
	    $folders = [ public_path( 'uploads' ) ];
	    foreach ( $folders as $folder ) {
	    	if(!file_exists($folder))
		        mkdir( $folder );
	    }

	    // Creating first user
	    $this->info('Admin registeration');
	    $name = $this->askUser('Enter your name (Full name)');
	    $email = $this->askUser('Enter your email');
	    $password = $this->askUser('Enter new password', 'secret');
	    User::create([
	    	'name' => $name,
	    	'email' => $email,
	    	'password' => Hash::make($password),
	    ]);
	    $this->info('Admin has been created successfully.');

	    $this->info( "Application installed successfully." );
    }

	public function askUser($msg, $type = 'ask', $required = true) {
		$attempt = 0;
		do {
			if ( $attempt > 0 ) {
				$this->warn( "This field is required" );
			}
			$value = $this->{$type}( $msg );
			$attempt ++;
		} while ( $required && empty( $value ) );

		return $value;
	}
}
