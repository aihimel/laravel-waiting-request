<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Facades\LWRequest;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlockerExpiryTest extends TestCase {
	private const CLASS_PATH  = 'App\Models\Booking';
	private const RESOURCE_ID = 7;

	private function cacheKey(): string
	{
		return config( 'waiting-request.cache_prefix' ) . self::CLASS_PATH . '_' . self::RESOURCE_ID;
	}

	public function testIsBlockedReturnsFalseAndDoesNotLogWhenNoEntry(): void
	{
		Log::shouldReceive( 'warning' )->never();

		$this->assertFalse( LWRequest::isBlocked( self::CLASS_PATH, self::RESOURCE_ID ) );
	}

	public function testIsBlockedReturnsTrueAndKeepsKeyWhenNotExpired(): void
	{
		Log::shouldReceive( 'warning' )->never();

		Cache::put( $this->cacheKey(), time() + 60 );

		$this->assertTrue( LWRequest::isBlocked( self::CLASS_PATH, self::RESOURCE_ID ) );
		$this->assertTrue( Cache::has( $this->cacheKey() ), 'Cache entry should still exist after a non-expired isBlocked() call.' );
	}

	public function testIsBlockedForgetsExpiredEntryAndEmitsWarning(): void
	{
		$expiredAt = time() - 1;
		Cache::put( $this->cacheKey(), $expiredAt );

		Log::shouldReceive( 'warning' )
			->once()
			->with(
				'Waiting-request blocker expired without being resolved',
				[
					'class_path'  => self::CLASS_PATH,
					'resource_id' => self::RESOURCE_ID,
					'expired_at'  => $expiredAt,
				]
			);

		$this->assertFalse( LWRequest::isBlocked( self::CLASS_PATH, self::RESOURCE_ID ) );
		$this->assertFalse( Cache::has( $this->cacheKey() ), 'Expired cache entry should be forgotten by isBlocked().' );
	}

	public function testIsBlockedDoesNotLogWhenBlockerResolvedManually(): void
	{
		Log::shouldReceive( 'warning' )->never();

		LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID );
		LWRequest::resolveBlocker( self::CLASS_PATH, self::RESOURCE_ID );

		$this->assertFalse( LWRequest::isBlocked( self::CLASS_PATH, self::RESOURCE_ID ) );
	}

	public function testExpiredBlockerCanBeReAddedAfterAccessTimeEviction(): void
	{
		Log::shouldReceive( 'warning' )->once();

		Cache::put( $this->cacheKey(), time() - 1 );

		// First isBlocked() call evicts the stale entry and logs.
		$this->assertFalse( LWRequest::isBlocked( self::CLASS_PATH, self::RESOURCE_ID ) );

		// A fresh blocker can now be added since the key was forgotten.
		$this->assertTrue( LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID ) );
		$this->assertTrue( LWRequest::isBlocked( self::CLASS_PATH, self::RESOURCE_ID ) );
	}

	public function testWhenResolvedReturnsTrueAfterBlockerExpires(): void
	{
		Log::shouldReceive( 'warning' )->once();

		Cache::put( $this->cacheKey(), time() - 1 );

		// whenResolved() polls isBlocked(); the first poll evicts the stale entry
		// and the loop should immediately report the resource as resolved.
		$this->assertTrue( LWRequest::whenResolved( self::CLASS_PATH, self::RESOURCE_ID, 1, 10 ) );
	}
}
