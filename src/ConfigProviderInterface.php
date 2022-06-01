<?php declare(strict_types=1);

namespace Satellite\Config;

interface ConfigProviderInterface {
    public function configure(): array;
}
