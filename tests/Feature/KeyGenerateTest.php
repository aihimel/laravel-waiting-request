<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\LWRequestService;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;
use ReflectionMethod;

class KeyGenerateTest extends TestCase
{
	public function testKyeGenerate(): void
	{
		$classPath = 'Fake\Class\Path';
		$resourceId = 123;

		$instance = new LWRequestService();
		$result = ( new ReflectionMethod( $instance, 'generateKey' ) )
			->invoke( $instance, $classPath, $resourceId );

		$this->assertSame( config( 'waiting-request.cache_prefix' ) . $classPath . '_' . $resourceId, $result );
	}
}