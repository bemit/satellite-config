<?php declare(strict_types=1);

namespace Satellite\Config;

class ConfigAggregator implements ConfigProviderInterface {
    protected ?string $cache;
    protected array $configurations = [];
    protected bool $mkdir_not_exists;

    /**
     * @param string|null $cache path to PHP file to use as cache
     */
    public function __construct(?string $cache = null, bool $mkdir_not_exists = false) {
        $this->cache = $cache === null ? null :
            (str_ends_with($cache, '.php') ? $cache : $cache . '.php');
        $this->mkdir_not_exists = $mkdir_not_exists;
    }

    protected function loadCache(): ?array {
        if($this->cache && file_exists($this->cache)) {
            return include $this->cache;
        }
        return null;
    }

    protected function writeCache(array $config): void {
        if($this->cache) {
            if($this->mkdir_not_exists) {
                $dir = dirname($this->cache);
                if(!is_dir($dir) && !mkdir($dir, 0775, true)) {
                    throw new \RuntimeException('Cache dir not created: ' . $dir);
                }
            }
            file_put_contents($this->cache, '<?php ' . PHP_EOL . 'return ' . var_export($config, true) . ';');
        }
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
                if(!is_scalar($value) || !in_array($value, $merged, true)) {
                    $merged[] = $value;
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Add configurations to be aggregated.
     *
     * @param callable[]|string[] $configurations
     * @return self
     */
    public function append(...$configurations): self {
        $this->configurations = array_merge($this->configurations, $configurations);
        return $this;
    }

    /**
     * Aggregation handling for nested providers, to be called from other aggregator, does not cache result.
     *
     * @return array
     * @internal
     */
    public function make(): array {
        $config_data = [];
        foreach($this->configurations as $i => $config) {
            if(is_string($config)) {
                $provider_instance = new $config();
                if($provider_instance instanceof ConfigProviderInterface) {
                    $config_partial = $provider_instance->configure();
                } else if(is_callable($provider_instance)) {
                    $config_partial = $provider_instance();
                } else {
                    throw new \InvalidArgumentException(
                        'configuration at pos `' . $i . '` is not compatible, not implementing `ConfigProviderInterface` and no valid `__invoke` result: ' . $config,
                    );
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

    /**
     * @inheritdoc
     */
    public function configure(): array {
        if($cached = $this->loadCache()) {
            return $cached;
        }

        $config_data = $this->make();

        $this->writeCache($config_data);

        return $config_data;
    }

    public static function envVar(string $var_name) {
        $var = getenv($var_name);
        if($var !== false) return $var;
        return $_ENV[$var_name] ?? false;
    }

    /**
     * Helper function to use FILE or TEXT env variables.
     *
     * @param string $var_name
     * @param string $file_suffix
     * @return string either the `getenv($var_name)` or the file contents of `getenv($var_name . $file_suffix)`
     * @deprecated use `envFileOrValue` instead
     */
    public static function envFileOrText(string $var_name, string $file_suffix = '__FILE'): string {
        $var_name_file = $var_name . $file_suffix;
        return is_string(getenv($var_name_file)) ?
            file_get_contents(getenv($var_name_file)) :
            getenv($var_name);
    }

    /**
     * Helper function to use FILE or TEXT env variables.
     */
    public static function envFileOrValue(string $var_name, false|string|array $var_value, false|string|array $var_file, bool $no_throw = false): null|string|array {
        if($var_file === false) {
            if($var_value === false) {
                if($no_throw) {
                    return null;
                }
                throw new \RuntimeException('expected env variable to exist: ' . $var_name);
            }
            return $var_value;
        }
        if(!is_string($var_file)) {
            if($no_throw) {
                return null;
            }
            throw new \RuntimeException('expected env variable file-path to be string: ' . $var_name);
        }
        if(!is_file($var_file)) {
            if($no_throw) {
                return null;
            }
            throw new \RuntimeException('expected env variable file-path to exist: ' . $var_name);
        }
        return file_get_contents($var_file);
    }

    /**
     * Helper function to use FILE env variables that point to JSON files
     *
     * @param string $var_name
     * @param string|false|array $env_val
     * @param string $folder
     * @param bool $no_throw
     * @param bool $assoc
     * @return mixed
     * @throws \JsonException
     */
    public static function envFileJson(string $var_name, string|false|array $env_val, string $folder = '', bool $no_throw = false, bool $assoc = false): mixed {
        if(!is_string($env_val) || $env_val === '') {
            if($no_throw) {
                return null;
            }
            throw new \RuntimeException('expected env variable to be string: ' . $var_name . ', ' . ($env_val === false ? 'not exists' : 'is array'));
        }
        $env_file = $folder . $env_val;
        if(!is_file($env_file)) {
            if($no_throw) {
                return null;
            }
            throw new \RuntimeException('file for env variable not exists: ' . $var_name);
        }
        return json_decode(file_get_contents($env_file), $assoc, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Helper function to use boolean flags saved in TEXT env variables
     *
     * @param false|array|string $var_name
     * @return bool
     */
    public static function envIsTrue(string $env_var): bool {
        return $env_var === 'yes' || $env_var === 'true' || $env_var === '1' || $env_var === 'on';
    }
}
