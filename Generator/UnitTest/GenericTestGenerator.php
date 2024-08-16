<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as AbstractDbCollection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
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
        if ($this->skipInstantationWithMocks($classStub->getFullQualifiedClassName())) {
            return "\$this->markTestSkipped('Test skipped because constructor is too complex for mocking');";
        }

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

                $parameterClass = '\\'.$parameterType->getName();
                $isClass = interface_exists($parameterClass) || class_exists($parameterClass);

                if (false === $isClass && $reflectionParameter->isDefaultValueAvailable()) {
                    continue;
                }

                if ($isClass) {
                    $parameterClassName = basename(str_replace('\\', '/', $parameterClass));
                    $test .= "\$$parameterName = \$this->createMock({$parameterClassName}::class);\n";
                    $test .= "//\${$parameterName}->method('getFoo')->willReturn('bar');\n";
                    $test .= "\n";
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

    private function skipInstantationWithMocks(string $className): bool
    {
        foreach ($this->getSkipClasses() as $skipClass) {
            if ($className === $skipClass || is_subclass_of($className, $skipClass)) {
                return true;
            }
        }

        return false;
    }

    private function getSkipClasses(): array
    {
        return [
            AbstractCollection::class,
            AbstractModel::class,
        ];
    }
}
