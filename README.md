# Laravel Waiting Request

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aihimel/laravel-waiting-request.svg?style=flat-square)](https://packagist.org/packages/aihimel/laravel-waiting-request)
[![Total Downloads](https://img.shields.io/packagist/dt/aihimel/laravel-waiting-request.svg?style=flat-square)](https://packagist.org/packages/aihimel/laravel-waiting-request)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%20--%208.4-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x%20--%2013.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Tests](https://github.com/aihimel/laravel-waiting-request/actions/workflows/phpunit.yml/badge.svg)](https://github.com/aihimel/laravel-waiting-request/actions/workflows/phpunit.yml)
[![Check Style](https://github.com/aihimel/laravel-waiting-request/actions/workflows/phpcs.yml/badge.svg)](https://github.com/aihimel/laravel-waiting-request/actions/workflows/phpcs.yml)
[![PHPStan](https://github.com/aihimel/laravel-waiting-request/actions/workflows/phpstan.yml/badge.svg)](https://github.com/aihimel/laravel-waiting-request/actions/workflows/phpstan.yml)
[![Security](https://github.com/aihimel/laravel-waiting-request/actions/workflows/security.yml/badge.svg)](https://github.com/aihimel/laravel-waiting-request/actions/workflows/security.yml)
[![codecov](https://codecov.io/gh/aihimel/laravel-waiting-request/branch/master/graph/badge.svg)](https://codecov.io/gh/aihimel/laravel-waiting-request)

A simple implementation for holding requests until a job or background process is finished. This package allows you to conditionally block requests and wait for them to be unblocked within a specified timeout.

## Installation

You can install the package via composer:

```bash
composer require aihimel/laravel-waiting-request
```

## Configuration

You can publish the configuration file using:

```bash
php artisan vendor:publish --tag="waiting-request-config"
```

The published configuration file `config/waiting-request.php` contains the following defaults:

```php
return [
    'cache_prefix' => env('LW_REQUEST_CACHE_PREFIX', 'lw_request_'),
    'timeout' => env('LW_REQUEST_MAX_WAITING_TIME', 5), // Default whenResolved timeout, in seconds
    'check_interval' => env('LW_REQUEST_CHECK_INTERVAL', 250), // whenResolved poll interval, in milliseconds
    'max_blocking_time' => env('LW_REQUEST_MAX_BLOCKING_TIME', 10), // Default blocker lifetime, in seconds
];
```

| Key | Env | Purpose |
| --- | --- | --- |
| `cache_prefix` | `LW_REQUEST_CACHE_PREFIX` | Prepended to every cache key the package writes. |
| `timeout` | `LW_REQUEST_MAX_WAITING_TIME` | How long `whenResolved()` waits before giving up. |
| `check_interval` | `LW_REQUEST_CHECK_INTERVAL` | How often `whenResolved()` polls between checks. |
| `max_blocking_time` | `LW_REQUEST_MAX_BLOCKING_TIME` | Default lifetime of a blocker. After this many seconds, the next `isBlocked()` / `whenResolved()` call evicts the blocker and emits a warning log. |

## Usage

### Basic Example

Suppose you have a resource that is being processed in the background (e.g., a large PDF generation or a data sync). You can block requests for this resource until the process is complete.

#### 1. Add a Blocker
In your controller or job where the background process starts:

```php
use Aihimel\LaravelWaitingRequest\Facades\LWRequest;

LWRequest::addBlocker(User::class, $user->id);

// Optionally override the lifetime (in seconds) per call.
// Non-positive values fall back to the `max_blocking_time` config default.
LWRequest::addBlocker(User::class, $user->id, 30);
```

#### 2. Wait for Resolution
In the request that needs to wait for the resource:

```php
use Aihimel\LaravelWaitingRequest\Facades\LWRequest;

// This will wait until the blocker is removed or the timeout is reached
$resolved = LWRequest::whenResolved(User::class, $user->id);

if ($resolved) {
    // Process the request
} else {
    // Handle timeout
}
```

#### 3. Resolve the Blocker
When the background process is finished:

```php
use Aihimel\LaravelWaitingRequest\Facades\LWRequest;

LWRequest::resolveBlocker(User::class, $user->id);
```

Calling `resolveBlocker()` explicitly is still the recommended path — it releases the lock immediately so waiting requests can proceed and avoids the warning log emitted by the auto-expiry backstop (see below).

### Checking if Blocked

You can also check if a resource is currently blocked without waiting:

```php
if (LWRequest::isBlocked(User::class, $user->id)) {
    // Resource is blocked
}
```

> **Note:** `isBlocked()` is **not** a pure read. If the blocker has passed its expiry, this call also forgets the cache entry and emits a `Log::warning`. Avoid placing it on a hot path that you expect to be side-effect-free.

### Blocker Lifetime

Every blocker now has a finite lifetime.

- The lifetime defaults to `max_blocking_time` from the config (10 seconds out of the box).
- You can override it per call via the third argument to `addBlocker()`.
- When the lifetime is reached, the next `isBlocked()` (and therefore `whenResolved()`) call:
  1. Deletes the cache entry, and
  2. Emits `Log::warning('Waiting-request blocker expired without being resolved', [...])` with `class_path`, `resource_id`, and `expired_at` in the context.

The log line goes through Laravel's `Log` facade, so it honors whatever channel and formatter your app has configured in `config/logging.php`.

If you have a background job that may take longer than 10 seconds, raise `LW_REQUEST_MAX_BLOCKING_TIME` (or pass an explicit TTL to `addBlocker()`) — otherwise the blocker will be auto-released while your job is still running, defeating the purpose of the lock.

## Upgrading

### From 1.x to 2.x

Version 2.x changes how blockers expire. The public method signatures stay backwards-compatible, but the runtime behavior changes:

- **Blockers now expire automatically.** In 1.x, a blocker lived in cache until `resolveBlocker()` was called. In 2.x, every blocker has a finite lifetime (default 10s, configurable via `LW_REQUEST_MAX_BLOCKING_TIME`). If your job can exceed that, set the env or pass a per-call TTL.
- **`isBlocked()` has side effects.** It deletes expired entries and emits a `Log::warning`.
- **The cache value shape changed** from `true` to a Unix expiry timestamp. Any pre-upgrade blocker keys left in cache will be read as integer `1`, treated as already-expired, deleted, and logged on first access. **Flush the cache (or at least the keys under `cache_prefix`) when deploying the upgrade** to avoid a one-time burst of warning logs and stale-blocker side effects.

See [`CHANGELOG.md`](CHANGELOG.md) for the full change list.

## Features & Bug Fixes

If you find any bugs or have a feature request, please [create an issue](https://github.com/aihimel/laravel-waiting-request/issues) on GitHub.

## Contributing

We welcome contributions! To become a contributor:

1. **Fork** the repository.
2. **Clone** your fork to your local machine.
3. **Create a branch** for your feature or bug fix.
4. **Commit** your changes with descriptive messages.
5. **Push** your branch to your fork.
6. **Submit a Pull Request** to the main repository.

### Local Development

This package comes with a Docker-based development environment.

#### Build container
```bash
docker compose --build
```

#### Run automated tests
```bash
docker exec laravel_waiting_request_app ./vendor/bin/phpunit
```

#### Run PHPCS code inspection
```bash
docker exec laravel_waiting_request_app ./vendor/bin/phpcs
```

#### Run PHPCBF auto-fixer
```bash
docker exec laravel_waiting_request_app ./vendor/bin/phpcbf
```

## License

The GPL-3.0-or-later License. Please see [License File](LICENSE) for more information.
