<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/mock/ConfigProvider.php';
require_once __DIR__ . '/mock/ConfigInvokableProvider.php';

final class AggregatorCacheTest extends TestCase {
    public function testAggregateMergeAndOverwriteAndCache(): void {
        if(file_exists(__DIR__ . '/tmp-aggregated.php')) {
            unlink(__DIR__ . '/tmp-aggregated.php');
        }
        $aggregator = new \Satellite\Config\ConfigAggregator(__DIR__ . '/tmp-aggregated.php');
        $aggregator->append(
            [
                'some_array' => [
                    'a' => 1,
                    'b' => 2,
                ],
            ],
            [
                'some_array' => [
                    'b' => 3,
                    'c' => 4,
                ],
            ],
        );

        $config1 = $aggregator->configure();

        $this->assertTrue(file_exists(__DIR__ . '/tmp-aggregated.php'));

        $this->assertEquals(
            [
                'some_array' => [
                    'a' => 1,
                    'b' => 3,
                    'c' => 4,
                ],
            ],
            $config1,
        );

        $config2 = $aggregator->configure();
        $this->assertEquals(
            [
                'some_array' => [
                    'a' => 1,
                    'b' => 3,
                    'c' => 4,
                ],
            ],
            $config2,
        );

        if(file_exists(__DIR__ . '/tmp-aggregated.php')) {
            unlink(__DIR__ . '/tmp-aggregated.php');
        }
    }
}
