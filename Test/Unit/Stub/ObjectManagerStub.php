<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Stub;

use Magento\Framework\ObjectManagerInterface;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

class ObjectManagerStub implements ObjectManagerInterface
{
    private array $objects = [];

    public function create($type, array $arguments = [])
    {
        $newArguments = [];

        $reflectionType = new ReflectionClass($type);
        foreach ($reflectionType->getConstructor()->getParameters() as $parameter) {
            $parameterName  = $parameter->getName();
            if (isset($arguments[$parameterName])) {
                $newArguments[] = $arguments[$parameterName];
                continue;
            }

            $parameterClass = $parameter->getType();
            if ($parameterClass instanceof ReflectionNamedType
                && array_key_exists($parameterClass->getName(), $this->objects)) {
                $newArguments[] = $this->objects[$parameterClass->getName()];
                continue;
            }

            throw new RuntimeException("Class {$type} has unknown parameter {$parameterName}");
        }

        return new $type(...array_values($newArguments));
    }

    public function get($type, array $arguments = [])
    {
        if (false === in_array($type, $this->objects)) {
            $this->objects[$type] = $this->create($type, $arguments);
        }

        return $this->objects[$type];
    }

    public function set($type, $object)
    {
        $this->objects[$type] = $object;
    }

    public function configure(array $configuration)
    {
    }
}
