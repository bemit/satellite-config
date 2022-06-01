<?php declare(strict_types=1);

namespace Satellite\Config;

class ConfigAggregator implements ConfigProviderInterface {
    protected ?string $cache;
    protected array $configurations = [];

    /**
     * @param string|null $cache path to PHP file to use as cache
     */
    public function __construct(?string $cache = null) {
        $this->cache = $cache;
    }

    protected function loadCache(): ?array {
        if($this->cache && file_exists($this->cache)) {
            return include $this->cache;
        }
        return null;
    }

    protected function writeCache(array $config): void {
        if($this->cache) {
            file_put_contents($this->cache, '<?php ' . PHP_EOL . 'return ' . var_export($config, true) . ';');
        }
    }

    /**
     * @param callable[]|string[] $configurations
     * @return self
     */
    public function append(...$configurations): self {
        $this->configurations = array_merge($this->configurations, $configurations);
        return $this;
    }

    protected static function aggregate(array $array1, array $array2): array {
        $merged = $array1;

        foreach($array2 as $key => &$value) {
            if(is_callable($value)) {
                $value = $value();
            }
            if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::aggregate($merged[$key], $value);
            } else if(is_numeric($key)) {
                if(!in_array($value, $merged, true)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * @return array
     */
    public function make(): array {
        $config_data = [];
        foreach($this->configurations as $i => $config) {
            if(is_string($config)) {
                /**
                 * @var ConfigProviderInterface $config
                 */
                $provider_instance = new $config();
                if($provider_instance instanceof ConfigProviderInterface) {
                    $config_partial = $provider_instance->configure();
                } else {
                    if(is_callable($provider_instance)) {
                        $config_partial = $provider_instance();
                    } else {
                        throw new \InvalidArgumentException(
                            'configuration at pos `' . $i . '` is not compatible, not implementing `ConfigProviderInterface` and no valid `__invoke` result: ' . $config,
                        );
                    }
                }
            } else if(is_callable($config)) {
                $config_partial = call_user_func($config, $config_data);
            } else if(is_array($config)) {
                $config_partial = $config;
            } else {
                throw new \InvalidArgumentException(
                    'configuration at pos `' . $i . '` is not compatible, must be string, callable or array but is ' . get_debug_type($config),
                );
            }

            $config_data = self::aggregate($config_data, $config_partial);
        }

        return $config_data;
    }

    public function configure(): array {
        if($cached = $this->loadCache()) {
            return $cached;
        }

        $config_data = $this->make();

        $this->writeCache($config_data);

        return $config_data;
    }

    public static function envFileOrText(string $var_name, string $file_suffix = '__FILE'): string {
        $var_name_file = $var_name . $file_suffix;
        return is_string(getenv($var_name_file)) ?
            file_get_contents(getenv($var_name_file)) :
            getenv($var_name);
    }
}
