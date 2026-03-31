<?php

namespace App\Core\Hooks;

use RuntimeException;

class HookManager
{
    /**
     * @var array<string, list<array{priority: int, callback: callable|array|string}>>
     */
    protected array $actions = [];

    /**
     * @var array<string, list<array{priority: int, callback: callable|array|string}>>
     */
    protected array $filters = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $registries = [];

    public function addAction(callable|array|string $callback, string $hook, int $priority = 10): void
    {
        $this->actions[$hook][] = [
            'priority' => $priority,
            'callback' => $callback,
        ];
    }

    public function doAction(string $hook, mixed ...$arguments): void
    {
        foreach ($this->sortedCallbacks($this->actions[$hook] ?? []) as $listener) {
            $this->resolveCallback($listener['callback'])(...$arguments);
        }
    }

    public function addFilter(callable|array|string $callback, string $hook, int $priority = 10): void
    {
        $this->filters[$hook][] = [
            'priority' => $priority,
            'callback' => $callback,
        ];
    }

    public function applyFilters(string $hook, mixed $value, mixed ...$arguments): mixed
    {
        $filteredValue = $value;

        foreach ($this->sortedCallbacks($this->filters[$hook] ?? []) as $listener) {
            $filteredValue = $this->resolveCallback($listener['callback'])($filteredValue, ...$arguments);
        }

        return $filteredValue;
    }

    public function register(string $registry, string $key, mixed $value): void
    {
        $this->registries[$registry][$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function registry(string $registry): array
    {
        return $this->registries[$registry] ?? [];
    }

    public function registryValue(string $registry, string $key, mixed $default = null): mixed
    {
        return $this->registries[$registry][$key] ?? $default;
    }

    /**
     * @param  list<array{priority: int, callback: callable|array|string}>  $callbacks
     * @return list<array{priority: int, callback: callable|array|string}>
     */
    protected function sortedCallbacks(array $callbacks): array
    {
        usort($callbacks, static fn (array $left, array $right): int => $left['priority'] <=> $right['priority']);

        return $callbacks;
    }

    protected function resolveCallback(callable|array|string $callback): callable
    {
        if (is_callable($callback)) {
            return $callback;
        }

        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);

            return [$this->resolveClass($class), $method];
        }

        if (is_string($callback)) {
            $instance = $this->resolveClass($callback);

            if (is_callable($instance)) {
                return $instance;
            }

            if (method_exists($instance, 'handle')) {
                return [$instance, 'handle'];
            }
        }

        if (is_array($callback) && isset($callback[0], $callback[1]) && is_string($callback[0])) {
            return [$this->resolveClass($callback[0]), $callback[1]];
        }

        if (is_callable($callback)) {
            return $callback;
        }

        throw new RuntimeException('Hook callback is not resolvable.');
    }

    protected function resolveClass(string $class): object
    {
        if (! class_exists($class)) {
            throw new RuntimeException("Hook class [{$class}] does not exist.");
        }

        return app($class);
    }
}
