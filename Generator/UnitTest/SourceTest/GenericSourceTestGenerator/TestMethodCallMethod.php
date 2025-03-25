<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator;

use ReflectionMethod;
use Yireo\TestGenerator\Model\ClassStub;

class TestMethodCallMethod
{
    public function get(ClassStub $classStub, ReflectionMethod $reflectionMethod): string
    {
        $className = $classStub->getClassName();
        $variableName = lcfirst($className);

        return <<<EOF
\$expected = '';
\${$variableName} = \$this->getInstance();
\$actual = \${$variableName}->{$reflectionMethod->getName()}();
\$this->assertInstanceOf(\$expected, \$actual);
EOF;
    }
}
