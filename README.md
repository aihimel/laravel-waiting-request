# Laravel Waiting Request
Conditionally wait for requests and retry

# Usage
The package is under development and no release has been made yet. Watch this project to stay updated.

## Contribution notes

### Important commands
Build container to run the package inside the container.
```bash
docker compose --build # to build the container
```

Run automated tests
```bash
docker exec laravel_waiting_request_app ./vendor/phpunit/phpunit/phpunit
```

Run phpcs code inspection
```bash
docker exec laravel_waiting_request_app ./vendor/bin/phpcs
```

Run phpcbf auto fixer
```bash
docker exec laravel_waiting_request_app ./vendor/bin/phpcbf
```
