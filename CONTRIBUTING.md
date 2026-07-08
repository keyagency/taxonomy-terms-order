# Contributing

## Development

``` bash
composer install
npm install
npm run build    # or: npm run dev
```

## Testing

``` bash
vendor/bin/phpunit
vendor/bin/pint --test
```

## Releasing

Compiled assets are not committed. Pushing a `v*` tag triggers the release workflow, which builds the assets and attaches a `dist.tar.gz` to the GitHub release. Consuming projects download it automatically on `composer install` via `pixelfear/composer-dist-plugin` (see `extra.download-dist` in `composer.json`).
