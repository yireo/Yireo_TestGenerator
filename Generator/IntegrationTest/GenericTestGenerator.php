<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

class GenericTestGenerator extends AbstractTestGenerator
{
    public function apply(ClassStub $classStub): bool
    {
        return true;
    }

    public function generate(ClassStub $classStub, ClassStub $testClassStub): PhpGenerator
    {
        $phpGenerator = parent::generate($classStub, $testClassStub);

        $phpGenerator->addClassMethod('testIfInstantiationWorks', $this->getTestIfInstantiationWorks($classStub->getClassName()));

        return $phpGenerator;
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
