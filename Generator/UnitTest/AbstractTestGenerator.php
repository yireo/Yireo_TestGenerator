<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest;

use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\GetObjectManager;

abstract class AbstractTestGenerator implements TestGeneratorInterface
{
    public function __construct(
        protected PhpGeneratorFactory $phpGeneratorFactory,
    ) {
    }

    abstract public function apply(ClassStub $classStub): bool;

    public function generate(ClassStub $classStub, ClassStub $testClassStub): string|PhpGenerator
    {
        $phpGenerator = $this->getPhpGenerator($testClassStub);
        $phpGenerator->addTrait(GetObjectManager::class);
        $phpGenerator->addUse($classStub->getFullQualifiedClassName());

        return $phpGenerator;
    }

    protected function getPhpGenerator(ClassStub $testClassStub): PhpGenerator
    {
        return $this->phpGeneratorFactory->create($testClassStub->getClassName(), $testClassStub->getNamespace());
    }
}
