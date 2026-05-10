<?php

namespace Aihimel\LaravelWaitingRequest;

class LWRequestService {
	public function addBlocker( string $classPath, int $resource_id ): bool
	{
		// add blocker code
		return true;
	}

	public function resolveBlocker( string $classPath, int $resource_id ): bool
	{
		// remove the blocker
		return true;
	}

	public function isBlocked( string $classPath, int $resource_id ): bool
	{
		// check if the resource si blocked
		return true;
	}
}