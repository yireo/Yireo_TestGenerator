<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest;

use ReflectionClass;
use ReflectionMethod;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\ObjectInstantiation;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\TestInstantiationMethod;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\TestMethodCallMethod;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\TestMockingMethod;
use Yireo\TestGenerator\Model\ClassStub;

class GenericSourceTestGenerator extends AbstractSourceTestGenerator
{
    public function __construct(
        private ObjectInstantiation $objectInstantiation,
        private TestMockingMethod $testMockingMethod,
        private TestInstantiationMethod $testInstantiationMethod,
        private TestMethodCallMethod $testMethodCallMethod,
        PhpGeneratorFactory $phpGeneratorFactory
    ) {
        parent::__construct($phpGeneratorFactory);
    }

    public function apply(ClassStub $classStub): bool
    {
        return true;
    }

    public function generate(ClassStub $classStub, ClassStub $testClassStub): string|PhpGenerator
    {
        $phpGenerator = parent::generate($classStub, $testClassStub);

        $phpGenerator->addClassMethod(
            'testMocking',
            $this->testMockingMethod->get($classStub)
        );

        $phpGenerator->addClassMethod(
            'testInstantiation',
            $this->testInstantiationMethod->get($classStub)
        );

        $this->addTestPerMethod($phpGenerator, $classStub);

        $phpGenerator->getClassType()->addMethod('getInstance')
            ->setReturnType($classStub->getFullQualifiedClassName())
            ->setPrivate()
            ->setBody($this->objectInstantiation->create($phpGenerator, $classStub));

        return $phpGenerator;
    }

    private function addTestPerMethod(PhpGenerator $phpGenerator, ClassStub $classStub): void
    {
        $reflectionClass = new ReflectionClass($classStub->getFullQualifiedClassName());
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            if ($this->skipMethod($reflectionMethod, $classStub)) {
                continue;
            }

            $phpGenerator->addClassMethod(
                'test'.ucfirst($reflectionMethod->getName()),
                $this->testMethodCallMethod->get($classStub, $reflectionMethod)
            );
        }
    }

    private function skipMethod(ReflectionMethod $reflectionMethod, ClassStub $classStub): bool
    {
        if (false === $reflectionMethod->isPublic()) {
            return true;
        }

        if ($reflectionMethod->class !== $classStub->getFullQualifiedClassName()) {
            return true;
        }

        return in_array($reflectionMethod->getName(), [
            '__construct'
        ]);
    }
}
