<?php

namespace Bitrix\Main\DI;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Path;

class ServiceLoader
{
    private $absoluteConfigPath;
    private $parameters = [];
    private $context; // 'admin' или 'user'

    public function __construct(string $configPath, string $context = 'user')
    {
        $this->absoluteConfigPath = $this->resolvePath($configPath);
        $this->context = $context;
    }

    public function register(): void
    {
        $config = $this->loadConfig();
        $this->processConfig($config);
    }

    private function resolvePath(string $path): string
    {
        if (Path::isAbsolute($path)) {
            return $path;
        }

        $documentRoot = Application::getDocumentRoot();
        $relativePath = ltrim($path, '/');
        return Path::combine($documentRoot, $relativePath);
    }

    private function loadConfig(): array
    {
        if (!file_exists($this->absoluteConfigPath)) {
            throw new \RuntimeException("Config file not found: {$this->absoluteConfigPath}");
        }

        $config = include $this->absoluteConfigPath;

        if (!is_array($config)) {
            throw new \RuntimeException("Config must return array");
        }

        return $config;
    }

    private function processConfig(array $config): void
    {
        $this->parameters = $config['parameters'] ?? [];
        $services = $config['services'] ?? $config;

        foreach ($services as $serviceId => $definition) {
            if ($serviceId === 'parameters') continue;
            $this->registerService($serviceId, $definition);
        }
    }

    private function registerService(string $serviceId, $definition): void
    {
        $locator = ServiceLocator::getInstance();

        // Обработка сервисов с прямым указанием constructor
        if (is_array($definition) && isset($definition['constructor'])) {
            $locator->addInstanceLazy($serviceId, [
                'constructor' => function() use ($definition) {
                    $result = $this->resolveValue($definition['constructor']);
                    // Если constructor возвращает callable, вызываем его
                    if (is_callable($result)) {
                        return $result();
                    }
                    return $result;
                }
            ]);
            return;
        }

        // Если сервис помечен как контекстный
        if (is_array($definition) && isset($definition['context'])) {
            $locator->addInstanceLazy($serviceId, [
                'constructor' => function() use ($definition) {
                    if ($this->context !== $definition['context']) {
                        throw new \RuntimeException("Service {$serviceId} not available in {$this->context} context");
                    }
                    return $this->createServiceInstance($definition);
                }
            ]);
        }
        // Обычная регистрация сервиса
        elseif (is_array($definition) && isset($definition['class'])) {
            $locator->addInstanceLazy($serviceId, [
                'constructor' => function () use ($definition) {
                    return $this->createServiceInstance($definition);
                }
            ]);
        } else {
            $locator->addInstance($serviceId, $this->resolveValue($definition));
        }
    }

    private function createServiceInstance(array $definition): object
    {
        $class = $definition['class'];
        $arguments = $this->resolveArguments($definition['arguments'] ?? []);

        if (!class_exists($class)) {
            throw new \RuntimeException("Class {$class} not found");
        }

        return new $class(...$arguments);
    }

    private function resolveArguments(array $arguments): array
    {
        return array_map([$this, 'resolveValue'], $arguments);
    }

    private function resolveValue($value)
    {
        if (is_string($value)) {
            $resolved = $this->resolveStringValue($value);
            return $resolved !== $value ? $resolved : $value;
        }

        if (is_array($value)) {
            return array_map([$this, 'resolveValue'], $value);
        }

        return $value;
    }


    private function resolveStringValue(string $value)
    {
        // Обработка вызовов вида @service->method(arg)
        if (preg_match('/^@([\w\.]+)->([\w]+)\(([^)]*)\)$/', $value, $matches)) {
            $service = ServiceLocator::getInstance()->get($matches[1]);
            $method = $matches[2];
            $args = $this->resolveArguments(array_map('trim', explode(',', $matches[3])));

            return $service->$method(...$args);
        }

        // Остальная существующая логика...
        if (preg_match('/^%([\w\.]+)%$/', $value, $matches)) {
            return $this->getNestedParameter($matches[1]);
        }

        if (strpos($value, '@') === 0) {
            return ServiceLocator::getInstance()->get(substr($value, 1));
        }

        return $value;
    }

    private function getNestedParameter(string $key)
    {
        // Проверяем наличие ключа в корне параметров
        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }

        // Если ключ содержит точки, пробуем разобрать как вложенный
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $current = $this->parameters;

            foreach ($keys as $k) {
                if (!is_array($current) || !array_key_exists($k, $current)) {
                    // Если дошли сюда, значит ключ не найден
                    break;
                }
                $current = $current[$k];
            }

            if ($current !== $this->parameters) { // Если что-то нашли
                return $current;
            }
        }

        // Если ничего не нашли - бросаем исключение
        throw new \RuntimeException("Parameter '{$key}' not found");
    }
}