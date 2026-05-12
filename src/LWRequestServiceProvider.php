<?php

namespace Aihimel\LaravelWaitingRequest;

use Aihimel\LaravelWaitingRequest\Enums\Accessor;
use Illuminate\Support\ServiceProvider;

class LWRequestServiceProvider extends ServiceProvider {
	public function register(): void
	{
		$this->mergeConfigFrom(
			__DIR__ . '/../config/waiting-request.php',
            'waiting-request'
		);

		$this->app->singleton( Accessor::ACCESS_KEY->value, function () {
			return new LWRequestService();
		});
	}

	public function boot(): void
	{
		if ($this->app->runningInConsole()) {
			$this->publishes( [
				__DIR__ . '/../config/waiting-request.php' => config_path( 'waiting-request.php' ),
			], 'waiting-request-config' );
		}
	}
}