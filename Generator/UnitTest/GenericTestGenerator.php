<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest;

use ReflectionClass;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Model\ClassStub;

class GenericTestGenerator
{
    private ?PhpGenerator $phpGenerator = null;

    public function __construct(
        private PhpGeneratorFactory $phpGeneratorFactory,
    ) {
    }

    public function generate(ClassStub $classStub, ClassStub $testClassStub): string
    {
        $testClassName = $testClassStub->getClassName();

        $this->phpGenerator = $this->phpGeneratorFactory->create($testClassName, $testClassStub->getNamespace());
        $this->phpGenerator->addUse($classStub->getFullQualifiedClassName());

        $this->phpGenerator->addClassMethod(
            'testMocking',
            $this->getTestMocking($classStub)
        );

        $this->phpGenerator->addClassMethod(
            'testInstantiationWithMocks',
            $this->getTestInstantiationWithMocks($classStub)
        );

        return $this->phpGenerator->output();
    }

    private function getTestMocking(ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $variableName = lcfirst($className);

        return <<<EOF
\${$variableName} = \$this->getMockBuilder({$className}::class)->disableOriginalConstructor()->getMock();
\$this->assertInstanceOf({$className}::class, \${$variableName});
// @todo: Extend upon this test
EOF;
    }

    private function getTestInstantiationWithMocks(ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $variableName = lcfirst($className);
        $reflectionClass = new ReflectionClass($classStub->getFullQualifiedClassName());

        $test = '';

        $constructorArguments = [];
        if ($reflectionClass->getConstructor()) {
            foreach ($reflectionClass->getConstructor()->getParameters() as $reflectionParameter) {
                $parameterName = $reflectionParameter->getName();
                $parameterType = $reflectionParameter->getType();
                if (null === $parameterType) {
                    continue;
                }

                if ($reflectionParameter->isDefaultValueAvailable()) {
                    continue;
                }

                $parameterClass = '\\'.$parameterType->getName();
                if (interface_exists($parameterClass) || class_exists($parameterClass)) {
                    $parameterClassName = basename(str_replace('\\', '/', $parameterClass));
                    $test .= "\$$parameterName = \$this->createMock({$parameterClassName}::class);\n\n";
                    $this->phpGenerator->addUse($parameterClass);
                }

                $constructorArguments[] = '$'.$parameterName;
            }
        }

        $constructorArguments = implode(', ', $constructorArguments);

        $test .= <<<EOF
\${$variableName} = new {$className}({$constructorArguments});
\$this->assertInstanceOf({$className}::class, \${$variableName});
// @todo: Extend upon this test and guarantee that instantiation works
EOF;

        return $test;
    }
}
