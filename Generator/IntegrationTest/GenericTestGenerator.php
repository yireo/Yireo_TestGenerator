<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\GetObjectManager;

class GenericTestGenerator
{
    public function __construct(
        private PhpGeneratorFactory $phpGeneratorFactory,
    ) {
    }

    public function generate(ClassStub $classStub, ClassStub $testClassStub): string
    {
        $testClassName = $testClassStub->getClassName();

        $phpGenerator = $this->phpGeneratorFactory->create($testClassName, $testClassStub->getNamespace());
        $phpGenerator->addTrait(GetObjectManager::class);
        $phpGenerator->addUse($classStub->getFullQualifiedClassName());

        $phpGenerator->addClassMethod('testIfInstantiationWorks', $this->getTestIfInstantiationWorks($classStub->getClassName()));

        return $phpGenerator->output();
    }

    private function getTestIfInstantiationWorks(string $className): string
    {
        $variableName = lcfirst($className);

        return <<<EOF
\${$variableName} = \$this->om()->get({$className}::class);
\$this->assertInstanceOf({$className}::class, \${$variableName});
// @todo: Extend upon this test
EOF;
    }
}
