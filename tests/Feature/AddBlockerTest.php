<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Facades\LWRequest;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;

class AddBlockerTest extends TestCase {
	public function testAddBlocker(): void
	{
		$this->assertTrue( LWRequest::addBlocker( 'App\Models\Booking', 2 ) );
		$this->assertTrue( LWRequest::isBlocked( 'App\Models\Booking', 2 ) );
	}

	public function testAddSameBlockerFail(): void
	{
		$this->assertTrue( LWRequest::addBlocker( 'App\Models\Booking', 2 ) );
		$this->assertFalse( LWRequest::addBlocker( 'App\Models\Booking', 2 ) );
	}

	public function testAddSameBlockerAfterResolvingTheFirstBlocker(): void
	{
		$this->assertTrue( LWRequest::addBlocker( 'App\Models\Booking', 2 ) );
		$this->assertTrue( LWRequest::resolveBlocker( 'App\Models\Booking', 2 ) );
		$this->assertTrue( LWRequest::addBlocker( 'App\Models\Booking', 2 ) );
	}
}