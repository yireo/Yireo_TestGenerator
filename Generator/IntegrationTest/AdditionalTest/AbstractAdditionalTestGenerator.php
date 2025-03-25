<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest;

use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\PhpGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Model\ClassStubFactory;

abstract class AbstractAdditionalTestGenerator implements AdditionalTestGeneratorInterface
{
    public function __construct(
        protected PhpGeneratorFactory $phpGeneratorFactory,
        protected ClassStubFactory $classStubFactory,
    ) {
    }

    abstract public function getTestStub(ModuleContext $moduleContext): ClassStub;

    protected function getPhpGenerator(ClassStub $testClassStub): PhpGeneratorInterface
    {
        return $this->phpGeneratorFactory->create($testClassStub);
    }

    protected function createTestStub(string $moduleName, string $fullQualifiedClassName): ClassStub
    {
        return $this->classStubFactory->create($moduleName, $fullQualifiedClassName);
    }
}
