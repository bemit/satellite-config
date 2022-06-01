<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/mock/ConfigProvider.php';
require_once __DIR__ . '/mock/ConfigInvokableProvider.php';

final class AggregatorAppendTest extends TestCase {
    public function testAppendClassesMake1(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
        $aggregator->append(
            ConfigProvider::class,
            ConfigInvokableProvider::class,
        );

        $this->assertEquals(
            [
                'some_array' => [
                    'commands' => [
                        __DIR__ . '/mock/Commands',
                        __DIR__ . '/mock/CommandsInvokable',
                    ],
                    'routes' => [
                        __DIR__ . '/mock/Routes',
                        __DIR__ . '/mock/RoutesInvokable',
                    ],
                ],
            ],
            $aggregator->make(),
        );
    }

    public function testAppendClassesMake2(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
        $aggregator->append(ConfigProvider::class);
        $aggregator->append(ConfigInvokableProvider::class);

        $this->assertEquals(
            [
                'some_array' => [
                    'commands' => [
                        __DIR__ . '/mock/Commands',
                        __DIR__ . '/mock/CommandsInvokable',
                    ],
                    'routes' => [
                        __DIR__ . '/mock/Routes',
                        __DIR__ . '/mock/RoutesInvokable',
                    ],
                ],
            ],
            $aggregator->make(),
        );
    }

    public function testAppendMixedArray(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
        $aggregator->append(
            ConfigProvider::class,
            [
                'some_array' => [
                    'commands' => [
                        __DIR__ . '/mock/CommandsArray',
                    ],
                    'routes' => [
                        __DIR__ . '/mock/RoutesArray',
                    ],
                ],
            ],
        );

        $this->assertEquals(
            [
                'some_array' => [
                    'commands' => [
                        __DIR__ . '/mock/Commands',
                        __DIR__ . '/mock/CommandsArray',
                    ],
                    'routes' => [
                        __DIR__ . '/mock/Routes',
                        __DIR__ . '/mock/RoutesArray',
                    ],
                ],
            ],
            $aggregator->make(),
        );
    }

    public function testAppendMixedFunction(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
        $aggregator->append(
            ConfigProvider::class,
            static fn() => [
                'some_array' => [
                    'commands' => [
                        __DIR__ . '/mock/CommandsFn',
                    ],
                    'routes' => [
                        __DIR__ . '/mock/RoutesFn',
                    ],
                ],
            ],
        );

        $this->assertEquals(
            [
                'some_array' => [
                    'commands' => [
                        __DIR__ . '/mock/Commands',
                        __DIR__ . '/mock/CommandsFn',
                    ],
                    'routes' => [
                        __DIR__ . '/mock/Routes',
                        __DIR__ . '/mock/RoutesFn',
                    ],
                ],
            ],
            $aggregator->make(),
        );
    }
}
