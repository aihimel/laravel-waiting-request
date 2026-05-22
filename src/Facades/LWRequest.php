<?php

namespace Aihimel\LaravelWaitingRequest\Facades;

use Aihimel\LaravelWaitingRequest\Enums\Accessor;
use Aihimel\LaravelWaitingRequest\LWRequestService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool addBlocker(string $classPath, int $resourceId, ?int $maxBlockingTime = null)
 * @method static bool resolveBlocker(string $classPath, int $resourceId)
 * @method static bool isBlocked(string $classPath, int $resourceId)
 * @method static bool whenResolved(string $classPath, int $resourceId, ?int $timeout = null, ?int $interval = null)
 *
 * @see LWRequestService
 */
class LWRequest extends Facade {
	protected static function getFacadeAccessor(): string
	{
		return Accessor::ACCESS_KEY->value;
	}
}