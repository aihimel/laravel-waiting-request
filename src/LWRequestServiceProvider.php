<?php

namespace Aihimel\LaravelWaitingRequest;

use Aihimel\LaravelWaitingRequest\Enums\Accessor;
use Illuminate\Support\ServiceProvider;

class LWRequestServiceProvider extends ServiceProvider {
	public function register(): void
	{
		$this->app->singleton( Accessor::ACCESS_KEY->value, function () {
			return new LWRequestService();
		});
	}
}