<?php

namespace Aihimel\LaravelWaitingRequest\Facades;

use Aihimel\LaravelWaitingRequest\Enums\Accessor;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool addBlocker(string $classPath, int $resourceId)
 * @method static bool resolveBlocker(string $classPath, int $resourceId)
 * @method static bool isBlocked(string $classPath, int $resourceId)
 * @method static bool whenResolved(string $classPath, int $resourceId, ?int $timeout = null, ?int $interval = null)
 *
 * @see \Aihimel\LaravelWaitingRequest\LWRequestService
 */
class LWRequest extends Facade {
	protected static function getFacadeAccessor(): string
	{
		return Accessor::ACCESS_KEY->value;
	}
}