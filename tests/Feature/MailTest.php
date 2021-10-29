<?php

namespace Tests\Feature;

use MailTracking;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MailTest extends TestCase
{
	use MailTracking;

	/**
     * A basic test example.
     *
     * @return void
     */
    public function testEmail()
    {
    	// TODO - Send dummy email and test if it works


	    $this->seeEmailWasSent()
	         ->seeEmailSubject('Hello World')
	         ->seeEmailTo('foo@bar.com')
	         ->seeEmailEquals('Click here to buy this jewelry.')
	         ->seeEmailContains('Unsubscribe');
    }
}
