<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Facades\LWRequest;
use Aihimel\LaravelWaitingRequest\Tests\TestCase;

/**
 * Tests that the facades are working as expected
 */
class FacadeTest extends TestCase {
	public function testAddBlocker(): void
	{
		$this->assertTrue( LWRequest::addBlocker( 'Fake\Class\Path', 2 ) );
	}

	public function testResolveBlocker(): void
	{
		LWRequest::addBlocker( 'Fake\Class\Path', 2 );
		$this->assertTrue( LWRequest::resolveBlocker( 'Fake\Class\Path', 2 ) );
	}

	public function testIsBlocked(): void
	{
		LWRequest::addBlocker( 'Fake\Class\Path', 2 );
		$this->assertTrue( LWRequest::isBlocked( 'Fake\Class\Path', 2 ) );
	}
}