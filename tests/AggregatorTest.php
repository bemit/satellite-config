<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/mock/ConfigProvider.php';
require_once __DIR__ . '/mock/ConfigInvokableProvider.php';

final class AggregatorTest extends TestCase {
    public function testAggregateMergeAndOverwrite(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
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

        $this->assertEquals(
            [
                'some_array' => [
                    'a' => 1,
                    'b' => 3,
                    'c' => 4,
                ],
            ],
            $aggregator->make(),
        );
    }

    public function testAggregateMergeAndDeduplicateNonAssoc(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
        $aggregator->append(
            [
                'some_array' => [
                    'b',
                    'a',
                ],
            ],
            [
                'some_array' => [
                    'b',
                    'q',
                    'c',
                ],
            ],
        );

        $this->assertEquals(
            [
                'some_array' => [
                    'b',
                    'a',
                    'q',
                    'c',
                ],
            ],
            $aggregator->make(),
        );
    }

    public function testAggregateNestedMergeAndOverwrite(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
        $aggregator->append(
            [
                'some_array' => [
                    'a' => [
                        'a.1' => 'var-0',
                        'a.2' => 'var-1',
                    ],
                ],
            ],
            [
                'some_array' => [
                    'a' => [
                        'a.2' => 'var-2',
                        'a.3' => 'var-3',
                    ],
                    'b' => [
                        'b.1' => true,
                    ],
                ],
            ],
        );

        $this->assertEquals(
            [
                'some_array' => [
                    'a' => [
                        'a.1' => 'var-0',
                        'a.2' => 'var-2',
                        'a.3' => 'var-3',
                    ],
                    'b' => [
                        'b.1' => true,
                    ],
                ],
            ],
            $aggregator->make(),
        );
    }

    public function testAggregateNestedFn(): void {
        $aggregator = new \Satellite\Config\ConfigAggregator();
        $aggregator->append(
            [
                'some_array' => [
                    'a' => [
                        'a.1' => 'var-0',
                        'a.2' => 'var-1',
                    ],
                ],
            ],
            [
                'some_array' => [
                    'a' => static fn() => [
                        'a.3' => 'var-3',
                    ],
                ],
            ],
            [
                'some_array' =>
                    static fn() => [
                        'b' => [
                            'b.1' => 'var-b',
                        ],
                    ],
            ],
        );

        $this->assertEquals(
            [
                'some_array' => [
                    'a' => [
                        'a.1' => 'var-0',
                        'a.2' => 'var-1',
                        'a.3' => 'var-3',
                    ],
                    'b' => [
                        'b.1' => 'var-b',
                    ],
                ],
            ],
            $aggregator->make(),
        );
    }
}
