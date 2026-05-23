<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Facades\LWRequest;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AddBlockerTest extends TestCase {
	private const CLASS_PATH  = 'App\Models\Booking';
	private const RESOURCE_ID = 2;

	private function cacheKey(): string
	{
		return config( 'waiting-request.cache_prefix' ) . self::CLASS_PATH . '_' . self::RESOURCE_ID;
	}

	public function testAddBlocker(): void
	{
		$this->assertTrue( LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID ) );
		$this->assertTrue( LWRequest::isBlocked( self::CLASS_PATH, self::RESOURCE_ID ) );
	}

	public function testAddSameBlockerFail(): void
	{
		$this->assertTrue( LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID ) );
		$this->assertFalse( LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID ) );
	}

	public function testAddSameBlockerAfterResolvingTheFirstBlocker(): void
	{
		$this->assertTrue( LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID ) );
		$this->assertTrue( LWRequest::resolveBlocker( self::CLASS_PATH, self::RESOURCE_ID ) );
		$this->assertTrue( LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID ) );
	}

	public function testAddBlockerStoresIntegerNotBoolean(): void
	{
		LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID );

		$stored = Cache::get( $this->cacheKey() );

		$this->assertIsInt( $stored );
		$this->assertNotSame( true, $stored );
	}

	public function testAddBlockerDefaultsToConfigMaxBlockingTime(): void
	{
		Config::set( 'waiting-request.max_blocking_time', 25 );

		$before = time();
		LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID );
		$after = time();

		$stored = Cache::get( $this->cacheKey() );

		$this->assertGreaterThanOrEqual( $before + 25, $stored );
		$this->assertLessThanOrEqual( $after + 25, $stored );
	}

	public function testAddBlockerHonoursExplicitPositiveTtl(): void
	{
		$before = time();
		LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID, 60 );
		$after = time();

		$stored = Cache::get( $this->cacheKey() );

		$this->assertGreaterThanOrEqual( $before + 60, $stored );
		$this->assertLessThanOrEqual( $after + 60, $stored );
	}

	public function testAddBlockerZeroTtlFallsBackToConfigDefault(): void
	{
		Config::set( 'waiting-request.max_blocking_time', 15 );

		$before = time();
		LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID, 0 );
		$after = time();

		$stored = Cache::get( $this->cacheKey() );

		$this->assertGreaterThanOrEqual( $before + 15, $stored );
		$this->assertLessThanOrEqual( $after + 15, $stored );
	}

	public function testAddBlockerNegativeTtlFallsBackToConfigDefault(): void
	{
		Config::set( 'waiting-request.max_blocking_time', 15 );

		$before = time();
		LWRequest::addBlocker( self::CLASS_PATH, self::RESOURCE_ID, -8 );
		$after = time();

		$stored = Cache::get( $this->cacheKey() );

		$this->assertGreaterThanOrEqual( $before + 15, $stored );
		$this->assertLessThanOrEqual( $after + 15, $stored );
	}
}