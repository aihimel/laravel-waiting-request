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
	): bool {
		return Cache::add(
			$this->generateKey( $classPath, $resourceId ),
			true,
		);
	}

	public function resolveBlocker( string $classPath, int $resourceId ): bool
	{
		return Cache::forget( $this->generateKey( $classPath, $resourceId ) );
	}

	public function isBlocked( string $classPath, int $resourceId ): bool
	{
		return Cache::has( $this->generateKey( $classPath, $resourceId ) );
	}
}