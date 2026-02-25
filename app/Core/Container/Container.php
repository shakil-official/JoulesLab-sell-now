<?php

namespace App\Core\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];

    public function __construct()
    {
        // Register the container itself
        $this->instances[self::class] = $this;
        $this->instances[ContainerInterface::class] = $this;
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (!isset($this->bindings[$id])) {
            throw new NotFoundException("No entry found for '{$id}'");
        }

        $concrete = $this->bindings[$id];

        if ($concrete instanceof \Closure) {
            $instance = $concrete($this);
        } elseif (is_string($concrete) && class_exists($concrete)) {
            $instance = $this->resolve($concrete);
        } else {
            $instance = $concrete;
        }

        if (in_array($id, $this->singletons, true)) {
            $this->instances[$id] = $instance;
        }

        return $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function bind(string $id, mixed $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $id;
        }

        $this->bindings[$id] = $concrete;
    }

    public function singleton(string $id, mixed $concrete = null): void
    {
        $this->bind($id, $concrete);
        $this->singletons[] = $id;
    }

    public function instance(string $id, mixed $instance): void
    {
        $this->instances[$id] = $instance;
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolve(string $className): object
    {
        $reflection = new \ReflectionClass($className);

        if (!$reflection->isInstantiable()) {
            throw new \Exception("Class {$className} is not instantiable");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $className();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve dependency for {$parameter->getName()} in {$className}");
                }
            } else {
                try {
                    $dependencies[] = $this->get($type->getName());
                } catch (NotFoundExceptionInterface $e) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new \Exception("Cannot resolve dependency {$type->getName()} for {$parameter->getName()} in {$className}");
                    }
                }
            }
        }

        try {
            return $reflection->newInstanceArgs($dependencies);
        } catch (\ReflectionException $e) {
            throw new \Exception("Failed to instantiate {$className}: " . $e->getMessage());
        }
    }
}
