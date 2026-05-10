<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Facades\LWRequest;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;

class FacadeTest extends TestCase {

	public function testAddBlocker(): void
	{
		$this->assertTrue( LWRequest::addBlocker( 'Fake\Class\Path', 2 ) );
	}

	public function testResolveBlocker(): void
	{
		$this->assertTrue( LWRequest::resolveBlocker( 'Fake\Class\Path', 2 ) );
	}

	public function testIsBlocked(): void
	{
		$this->assertTrue( LWRequest::isBlocked( 'Fake\Class\Path', 2 ) );
	}
}