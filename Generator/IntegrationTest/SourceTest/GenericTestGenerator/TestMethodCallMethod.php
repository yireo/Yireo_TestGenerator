<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\GenericTestGenerator;

use PHPStan\BetterReflection\Reflection\Adapter\ReflectionUnionType;
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

        $reflectedReturnType = $reflectionMethod->getReturnType();
        $returnType = null;
        if (is_object($reflectedReturnType) && false === $reflectedReturnType instanceof \ReflectionUnionType) {
            $returnType = $reflectedReturnType->getName();
        }

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
