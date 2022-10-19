<?php declare(strict_types=1);

namespace Satellite\Config;

interface ConfigProviderInterface {
    /**
     * Aggregate, and if configured cache, the provided configurations.
     *
     * @return array the aggregated config array
     */
    public function configure(): array;
}
