<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Stub;

use Magento\Framework\ObjectManagerInterface;

class ObjectManagerStub implements ObjectManagerInterface
{
    private array $objects = [];

    public function create($type, array $arguments = [])
    {
        return new $type(...array_values($arguments));
    }

    public function get($type, array $arguments = [])
    {
        if (false === in_array($type, $this->objects)) {
            $this->objects[$type] = $this->create($type, $arguments);
        }

        return $this->objects[$type];
    }

    public function configure(array $configuration)
    {
    }
}
