<?php declare(strict_types=1);

class ConfigInvokableProvider {
    public function __invoke(): array {
        return [
            'some_array' => [
                'commands' => [
                    __DIR__ . '/CommandsInvokable',
                ],
                'routes' => [
                    __DIR__ . '/RoutesInvokable',
                ],
            ],
        ];
    }
}
