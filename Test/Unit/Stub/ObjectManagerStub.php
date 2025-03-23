<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Stub;

use Magento\Framework\ObjectManagerInterface;

class ObjectManagerStub implements ObjectManagerInterface
{
    private array $objects = [];

    public function create($type, array $arguments = [])
    {
        $newArguments = [];

        $reflectionType = new \ReflectionClass($type);
        foreach ($reflectionType->getConstructor()->getParameters() as $parameter) {
            $parameterName  = $parameter->getName();
            if (isset($arguments[$parameterName])) {
                $newArguments[] = $arguments[$parameterName];
                continue;
            }

            $newArguments[] = $this->objects[$parameter->getDeclaringClass()->getName()];
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
