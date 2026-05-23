<?php

namespace Aihimel\LaravelWaitingRequest\Tests\Feature;

use Aihimel\LaravelWaitingRequest\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ConfigurationTest extends TestCase
{
    public function testConfigurationCascading()
    {
        // Check default value
        $this->assertEquals('lw_request_', config('waiting-request.cache_prefix'));

        // Override value
        config(['waiting-request.cache_prefix' => 'custom_prefix_']);

        // Check overridden value
        $this->assertEquals('custom_prefix_', config('waiting-request.cache_prefix'));
    }

    public function testMaxBlockingTimeDefault()
    {
        $this->assertEquals(10, config('waiting-request.max_blocking_time'));
    }

    public function testMaxBlockingTimeCanBeOverridden()
    {
        config(['waiting-request.max_blocking_time' => 45]);
        $this->assertEquals(45, config('waiting-request.max_blocking_time'));
    }

    public function testConfigurationCanBePublished()
    {
        $configPath = config_path('waiting-request.php');

        // Ensure the file does not exist before publishing
        if (File::exists($configPath)) {
            File::delete($configPath);
        }

        $this->artisan('vendor:publish', [
            '--tag' => 'waiting-request-config',
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($configPath));

        // Cleanup
        File::delete($configPath);
    }
}
