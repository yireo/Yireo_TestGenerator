<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator;

use Yireo\TestGenerator\Model\ClassStub;

class TestMockingMethod
{
    public function get(ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $variableName = lcfirst($className);

        return <<<EOF
\${$variableName} = \$this->getMockBuilder({$className}::class)->disableOriginalConstructor()->getMock();
\$this->assertInstanceOf({$className}::class, \${$variableName});
EOF;
    }
}
