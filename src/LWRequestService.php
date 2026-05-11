<?php

namespace Aihimel\LaravelWaitingRequest;

use Illuminate\Support\Facades\Cache;

class LWRequestService {
	protected function generateKey( string $classPath, int $resourceId ): string
	{
		return $classPath . "_" . $resourceId;
	}

	public function addBlocker(
        string $classPath,
        int $resourceId,
        int $ttl = 10,
	): bool {
		Cache::add(
			$this->generateKey( $classPath, $resourceId ),
			true,
			$ttl
		);
		return true;
	}

	public function resolveBlocker( string $classPath, int $resourceId ): bool
	{
		// remove the blocker
		return true;
	}

	public function isBlocked( string $classPath, int $resourceId ): bool
	{
		// check if the resource si blocked
		return true;
	}
}