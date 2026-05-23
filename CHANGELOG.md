# Changelog

All notable changes to `aihimel/laravel-waiting-request` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-05-22

### Added
- `max_blocking_time` config entry (env `LW_REQUEST_MAX_BLOCKING_TIME`, default `10` seconds) — sets the default lifetime of a blocker.
- Optional third argument `?int $maxBlockingTime` on `LWRequest::addBlocker(...)` for overriding the lifetime per call. Non-positive values fall back to the config default.
- Auto-expiry: when a blocker's lifetime is reached, the next `isBlocked()` (or `whenResolved()`) call deletes the cache entry and emits `Log::warning('Waiting-request blocker expired without being resolved', [...])` with `class_path`, `resource_id`, and `expired_at` in the context. The log uses Laravel's `Log` facade and honors the application's configured channels.

### Changed
- **Blockers are no longer persistent by default.** In 1.x a blocker lived in cache until `resolveBlocker()` was called explicitly. In 2.x every blocker has a finite lifetime drawn from `max_blocking_time`.
- **Cache value shape changed** from boolean `true` to an integer Unix expiry timestamp. The cache key is written with no engine TTL; eviction is performed at access time inside `isBlocked()`.
- `isBlocked()` is no longer a pure read. When the stored expiry is in the past, it forgets the cache key and emits a warning log before returning `false`.

### Upgrading from 1.x

Method signatures remain backwards-compatible, but the runtime behavior is not. Before deploying:

1. **Pick a `max_blocking_time` that covers your longest expected blocker.** The default is 10 seconds. If any background process protected by a blocker may take longer than that, set `LW_REQUEST_MAX_BLOCKING_TIME` accordingly, or pass an explicit TTL to `addBlocker(...)` at the call site. Otherwise the blocker will be auto-released mid-process and the race the package exists to prevent becomes possible.
2. **Flush pre-existing blocker keys from cache as part of the deploy.** Pre-upgrade entries store `true` as their value. After upgrade `isBlocked()` reads `(int) true === 1`, treats every such key as already expired, deletes it, and emits a warning log. Flushing the keys under `cache_prefix` (or the whole cache store) avoids a one-time burst of warnings and prevents stale blockers from being reported as released.
3. **Review callers of `isBlocked()`.** It now writes to the cache and emits log entries on expiry. If you call it from a context that was assumed to be side-effect-free, move that logic or guard it.

### Tests
- `WhenResolvedTest::testWhenResolvedReturnsTrueIfResourceIsUnblockedWithinTimeout` updated to mock `Cache::get()` returning an expiry timestamp (then `null`) instead of the previous `Cache::has()` boolean sequence.

## [1.0.2] - prior

Last release before the auto-expiry behavior was introduced. Blockers persisted in cache until `resolveBlocker()` was called explicitly; cache values were stored as boolean `true`.

[2.0.0]: https://github.com/aihimel/laravel-waiting-request/compare/v1.0.2...v2.0.0
[1.0.2]: https://github.com/aihimel/laravel-waiting-request/releases/tag/v1.0.2
