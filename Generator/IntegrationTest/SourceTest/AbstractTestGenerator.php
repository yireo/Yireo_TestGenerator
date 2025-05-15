<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\SourceTest;

use Magento\Framework\App\State;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\PhpGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\GetObjectManager;

abstract class AbstractTestGenerator implements SourceTestGeneratorInterface
{
    public function __construct(
        protected PhpGeneratorFactory $phpGeneratorFactory,
        protected State $appState
    ) {
    }

    abstract public function apply(ClassStub $classStub): bool;

    public function generate(ClassStub $classStub, ClassStub $testClassStub): PhpGeneratorInterface
    {
        $phpGenerator = $this->getPhpGenerator($testClassStub);
        $phpGenerator->addTrait(GetObjectManager::class);
        $phpGenerator->addUse($classStub->getFullQualifiedClassName());

        return $phpGenerator;
    }

    protected function getPhpGenerator(ClassStub $testClassStub): PhpGeneratorInterface
    {
        return $this->phpGeneratorFactory->create($testClassStub);
    }
}
