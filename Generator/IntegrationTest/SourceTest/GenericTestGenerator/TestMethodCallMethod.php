<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\GenericTestGenerator;

use ReflectionMethod;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

class TestMethodCallMethod
{
    public function get(
        PhpGenerator $phpGenerator,
        ClassStub $classStub,
        ReflectionMethod $reflectionMethod
    ): string {
        $className = $classStub->getClassName();
        $variableName = lcfirst($className);


        $returnType = $reflectionMethod->getReturnType()?->getName();

        if (!empty($returnType)
            && (class_exists('\\'.$returnType) || interface_exists('\\'.$returnType))) {
            $phpGenerator->addUse($returnType);
            $assertion = "\$this->assertInstanceOf(\\{$returnType}::class, \$actual)";
        } elseif ($returnType === 'array') {
            $assertion = "\$this->assertSame([], \$actual)";
        } else {
            $assertion = "\$this->assertEquals('', \$actual)";
        }

        return <<<EOF
\${$variableName} = \$this->getInstance();
\$actual = \${$variableName}->{$reflectionMethod->getName()}();
$assertion;
EOF;
    }
}
