<?php

namespace Aihimel\LaravelWaitingRequest;

use Illuminate\Support\Facades\Cache;

class LWRequestService {
	/**
	 * Generating a unique key to store the blocker in cache
	 *
	 * @param string $classPath
	 * @param int    $resourceId
	 *
	 * @return string
	 */
	protected function generateKey( string $classPath, int $resourceId ): string
	{
		return config( 'waiting-request.cache_prefix' ) . $classPath . "_" . $resourceId;
	}

	/**
	 * Create a blocker for a resource
	 *
	 * @param string   $classPath
	 * @param int      $resourceId
	 * @param int|null $maxBlockingTime TTL in seconds.
	 *   null (default) falls back to the `waiting-request.max_blocking_time` config value.
	 *   Any positive integer is used as-is.
	 *   Zero or a negative integer disables the TTL (blocker persists until resolved).
	 *
	 * @return bool
	 */
	public function addBlocker( string $classPath, int $resourceId, ?int $maxBlockingTime = null ): bool
	{
		$ttl = $maxBlockingTime ?? (int) config( 'waiting-request.max_blocking_time', 10 );
		$ttl = $ttl > 0 ? $ttl : null;

		return Cache::add(
			$this->generateKey( $classPath, $resourceId ),
			true,
			$ttl
		);
	}

	/**
	 * Resolve or remove a blocker
	 *
	 * @param string $classPath
	 * @param int    $resourceId
	 *
	 * @return bool
	 */
	public function resolveBlocker( string $classPath, int $resourceId ): bool
	{
		return Cache::forget( $this->generateKey( $classPath, $resourceId ) );
	}

	/**
	 * Check if a resource is blocked
	 *
	 * @param string $classPath
	 * @param int    $resourceId
	 *
	 * @return bool
	 */
	public function isBlocked( string $classPath, int $resourceId ): bool
	{
		return Cache::has( $this->generateKey( $classPath, $resourceId ) );
	}

	/**
	 * Wait till a blocker is resolved
	 *
	 * @param string   $classPath
	 * @param int      $resourceId
	 * @param int|null $timeout timeout in seconds
	 * @param int|null $interval interval in miliseconds
	 *
	 * @return bool
	 */
	public function whenResolved( string $classPath, int $resourceId, ?int $timeout = null, ?int $interval = null ): bool
	{
		$timeout  = $timeout ?? config( 'waiting-request.timeout', 5 );
		$interval = $interval ?? config( 'waiting-request.check_interval', 250 );

		$startTime = microtime( true );
		$endTime   = $startTime + $timeout;

		while (microtime( true ) < $endTime) {
			if (! $this->isBlocked( $classPath, $resourceId )) {
				return true;
			}

			usleep( $interval * 1000 );
		}

		return ! $this->isBlocked( $classPath, $resourceId );
	}
}