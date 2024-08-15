<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Model;

use Magento\Framework\ObjectManagerInterface;

class ClassStubFactory
{
    public function __construct(
        private ObjectManagerInterface $objectManager
    ) {
    }

    public function create(string $moduleName, string $fullQualifiedClassName): ClassStub
    {
        return $this->objectManager->create(ClassStub::class, [
            'moduleName' => $moduleName,
            'fullQualifiedClassName' => $fullQualifiedClassName,
        ]);
    }

    public function createTest(ClassStub $original, string $type = 'Integration')
    {
        $type = ucfirst($type);
        $testClass = $original->getModuleClassPrefix()
            .'\\Test\\'.$type
            .'\\'.$original->getRelativeNamespace()
            .'\\'.$original->getClassName().'Test';

        return $this->create(
            $original->getModuleName(),
            $testClass
        );
    }
}
