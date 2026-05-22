<?php

namespace Aihimel\LaravelWaitingRequest;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
	 * Create a blocker for a resource.
	 *
	 * The cache value is the Unix expiry timestamp; no engine TTL is applied.
	 * Eviction happens at access time inside isBlocked().
	 *
	 * @param string   $classPath
	 * @param int      $resourceId
	 * @param int|null $maxBlockingTime TTL in seconds.
	 *   null (default) or any non-positive value falls back to the
	 *   `waiting-request.max_blocking_time` config (default 10s).
	 *
	 * @return bool
	 */
	public function addBlocker( string $classPath, int $resourceId, ?int $maxBlockingTime = null ): bool
	{
		$ttl = $maxBlockingTime ?? (int) config( 'waiting-request.max_blocking_time', 10 );
		if ($ttl <= 0) {
			$ttl = (int) config( 'waiting-request.max_blocking_time', 10 );
		}

		return Cache::add(
			$this->generateKey( $classPath, $resourceId ),
			time() + $ttl
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
	 * Check if a resource is blocked.
	 *
	 * Note: this is the eviction point. If the stored expiry has passed, the
	 * cache entry is deleted and a warning is logged via Laravel's Log facade
	 * (which honors the application's configured log channels).
	 *
	 * @param string $classPath
	 * @param int    $resourceId
	 *
	 * @return bool
	 */
	public function isBlocked( string $classPath, int $resourceId ): bool
	{
		$key       = $this->generateKey( $classPath, $resourceId );
		$expiresAt = Cache::get( $key );

		if ($expiresAt === null) {
			return false;
		}

		if (time() >= (int) $expiresAt) {
			Cache::forget( $key );
			Log::warning(
				'Waiting-request blocker expired without being resolved',
				[
					'class_path'  => $classPath,
					'resource_id' => $resourceId,
					'expired_at'  => (int) $expiresAt,
				]
			);
			return false;
		}

		return true;
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