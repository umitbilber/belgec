<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use RuntimeException;

class Container
{
    private array $bindings = [];
    private array $singletons = [];
    private array $instances = [];

    public function bind(string $abstract, string $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, string $concrete): void
    {
        $this->singletons[$abstract] = $concrete;
    }

    public function make(string $abstract): object
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->singletons[$abstract]
            ?? $this->bindings[$abstract]
            ?? $abstract;

        $instance = $this->build($concrete);

        if (isset($this->singletons[$abstract])) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    private function build(string $concrete): object
    {
        $reflection = new ReflectionClass($concrete);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("[$concrete] instantiate edilemiyor.");
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $concrete();
        }

        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();

            if ($type && !$type->isBuiltin()) {
                $params[] = $this->make($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new RuntimeException(
                    "[$concrete] için [{$param->getName()}] parametresi çözülemiyor."
                );
            }
        }

        return $reflection->newInstanceArgs($params);
    }
}