<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Facades\LWRequest;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;

class RemoveBlockerTest extends TestCase {
	public function testRemoveBlocker(): void
	{
		$this->assertTrue( LWRequest::addBlocker( 'App\Models\Booking', 2 ) );
		$this->assertTrue( LWRequest::resolveBlocker( 'App\Models\Booking', 2 ) );
		$this->assertFalse( LWRequest::isBlocked( 'App\Models\Booking', 2 ) );
	}

	public function testUnavailableBlockerRemovalFail(): void
	{
		$this->assertTrue( LWRequest::addBlocker( 'App\Models\Booking', 2 ) );
		$this->assertTrue( LWRequest::resolveBlocker( 'App\Models\Booking', 2 ) );
		$this->assertFalse( LWRequest::resolveBlocker( 'App\Models\Booking', 2 ) );
	}
}