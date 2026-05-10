<?php

namespace Aihimel\LaravelWaitingRequest\Facades;

use Aihimel\LaravelWaitingRequest\Enums\Accessor;
use Illuminate\Support\Facades\Facade;

class LWRequest extends Facade {
	protected static function getFacadeAccessor(): string
	{
		return Accessor::ACCESS_KEY->value;
	}
}