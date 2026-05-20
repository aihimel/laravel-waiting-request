<?php

namespace Aihimel\LaravelWaitingRequest\Tests;

use Aihimel\LaravelWaitingRequest\LWRequestServiceProvider;
use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    protected function getPackageProviders($app)
    {
        return [
            LWRequestServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LWRequest' => 'Aihimel\LaravelWaitingRequest\Facades\LWRequest',
        ];
    }
}
