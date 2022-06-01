<?php declare(strict_types=1);

class ConfigProvider implements \Satellite\Config\ConfigProviderInterface {
    public function configure(): array {
        return [
            'some_array' => [
                'commands' => [
                    __DIR__ . '/Commands',
                ],
                'routes' => [
                    __DIR__ . '/Routes',
                ],
            ],
        ];
    }
}
