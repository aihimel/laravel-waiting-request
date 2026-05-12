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
		return $classPath . "_" . $resourceId;
	}

	/**
	 * Create a blocker for a resource
	 *
	 * @param string $classPath
	 * @param int    $resourceId
	 *
	 * @return bool
	 */
	public function addBlocker( string $classPath, int $resourceId ): bool {
		return Cache::add(
			$this->generateKey( $classPath, $resourceId ),
			true,
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
}