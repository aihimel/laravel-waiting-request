<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Facades\LWRequest;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class WhenResolvedTest extends TestCase {
	public function testWhenResolvedReturnsTrueIfInitiallyUnblocked(): void
	{
		$this->assertTrue( LWRequest::whenResolved( 'App\Models\User', 1 ) );
	}

	public function testWhenResolvedReturnsFalseIfTimeoutReached(): void
	{
		LWRequest::addBlocker( 'App\Models\User', 1 );

		$startTime = microtime( true );
		// Set a short timeout for the test
		$result = LWRequest::whenResolved( 'App\Models\User', 1, 1, 100 );
		$endTime = microtime( true );

		$this->assertFalse( $result );
		$this->assertGreaterThanOrEqual( 1, $endTime - $startTime );
	}

	public function testWhenResolvedRespectsConfigDefaults(): void
	{
		Config::set( 'waiting-request.timeout', 1 );
		Config::set( 'waiting-request.check_interval', 100 );

		LWRequest::addBlocker( 'App\Models\User', 1 );

		$startTime = microtime( true );
		$result    = LWRequest::whenResolved( 'App\Models\User', 1 );
		$endTime   = microtime( true );

		$this->assertFalse( $result );
		$this->assertGreaterThanOrEqual( 1, $endTime - $startTime );
	}

	public function testWhenResolvedReturnsTrueIfResourceIsUnblockedWithinTimeout(): void
	{
		// isBlocked() reads Cache::get() and treats the value as a Unix expiry
		// timestamp. Simulate two "still blocked" reads (future timestamp), then
		// a "resolved" read (null = key absent).
		$future = time() + 60;

		Cache::shouldReceive('get')
			->times(3)
			->andReturn($future, $future, null);

		$result = LWRequest::whenResolved( 'App\Models\User', 1, 2, 10 );

		$this->assertTrue( $result );
	}
}
