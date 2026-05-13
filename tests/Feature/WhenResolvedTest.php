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
		// Since PHP is single-threaded, we'll use a mock to simulate the resource being unblocked
		// after a few checks.

		// The whenResolved method calls isBlocked, which calls Cache::has.
		// We want isBlocked to return true twice, then false.

		// However, isBlocked uses Cache::has.
		// We can mock Cache::has.

		Cache::shouldReceive('has')
			->times(3)
			->andReturn(true, true, false);

		// We need to make sure the key generated matches what we expect or just allow any key.
		// The key is generated using config('waiting-request.cache_prefix') which is 'lw_request_' by default.

		$result = LWRequest::whenResolved( 'App\Models\User', 1, 2, 10 );

		$this->assertTrue( $result );
	}
}
