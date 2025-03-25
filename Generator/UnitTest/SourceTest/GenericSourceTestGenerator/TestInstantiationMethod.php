<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator;

use Yireo\TestGenerator\Model\ClassStub;

class TestInstantiationMethod
{
    public function get(ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $variableName = lcfirst($className);

        return <<<EOF
\${$variableName} = \$this->getInstance();
\$this->assertInstanceOf({$className}::class, \${$variableName});
EOF;
    }
}
