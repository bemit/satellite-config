# Orbiter\Satellite Config

[![Latest Stable Version](https://poser.pugx.org/orbiter/satellite-config/version.svg)](https://packagist.org/packages/orbiter/satellite-config)
[![Latest Unstable Version](https://poser.pugx.org/orbiter/satellite-config/v/unstable.svg)](https://packagist.org/packages/orbiter/satellite-config)
[![codecov](https://codecov.io/gh/bemit/satellite-config/branch/master/graph/badge.svg?token=N376EQ2T5O)](https://codecov.io/gh/bemit/satellite-config)
[![Total Downloads](https://poser.pugx.org/orbiter/satellite-config/downloads.svg)](https://packagist.org/packages/orbiter/satellite-config)
[![Github actions Build](https://github.com/bemit/satellite-config/actions/workflows/blank.yml/badge.svg)](https://github.com/bemit/satellite-config/actions)
[![PHP Version Require](http://poser.pugx.org/orbiter/satellite-config/require/php)](https://packagist.org/packages/orbiter/satellite-config)

- simple caching aggregator with caching

Check [satellite-app](https://github.com/bemit/satellite-app) for a ready to use template, or install just this library:

```shell
composer require orbiter/satellite-config
```

- [ConfigProvider](./tests/mock/ConfigInvokableProvider.php) implementing `\Satellite\Config\ConfigProviderInterface`
- [ConfigInvokableProvider](./tests/mock/ConfigInvokableProvider.php) using `__invoke(): array`

```injectablephp
$aggregator = new \Satellite\Config\ConfigAggregator($is_prod ? __DIR__ . '/tmp/config_aggregated.php' : null);
$aggregator->append( 
    ConfigProvider::class,
    ConfigInvokableProvider::class,
    // when using cache, is only executed when on warm-up
    static fn() => [
        'some_array' => [
            'a' => [],
        ],
    ],
    // pass down arrays:
    [
        'some_array' => [
            'b' => [],
            // functions can also be used nested (only executed when on warm-up)
            'c' => static fn() => [],
        ]
    ],
);
// just aggregate:
$config = $aggregator->make();
// aggregate and use cache (if configured):
$config = $aggregator->configure();
```

## Dev Notices

Commands to set up and run e.g. tests:

```bash
# on windows:
docker run -it --rm -v %cd%:/app composer install

docker run -it --rm -v %cd%:/var/www/html php:8.1-cli-alpine sh

docker run --rm -v %cd%:/var/www/html php:8.1-cli-alpine sh -c "cd /var/www/html && ./vendor/bin/phpunit --testdox -c phpunit-ci.xml --bootstrap vendor/autoload.php"

# on unix:
docker run -it --rm -v `pwd`:/app composer install

docker run -it --rm -v `pwd`:/var/www/html php:8.1-cli-alpine sh

docker run --rm -v `pwd`:/var/www/html php:8.1-cli-alpine sh -c "cd /var/www/html && ./vendor/bin/phpunit --testdox -c phpunit-ci.xml --bootstrap vendor/autoload.php"
```

## Versions

This project adheres to [semver](https://semver.org/), **until `1.0.0`** and beginning with `0.1.0`: all `0.x.0` releases are like MAJOR releases and all `0.0.x` like MINOR or PATCH, modules below `0.1.0` should be considered experimental.

## License

This project is free software distributed under the [**MIT LICENSE**](LICENSE).

### Contributors

By committing your code to the code repository you agree to release the code under the MIT License attached to the repository.

***

Maintained by [Michael Becker](https://mlbr.xyz)
