<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Magento\TestFramework\Fixture\Config as ConfigFixture;
use ReflectionMethod;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

class ConfigTestGenerator extends AbstractTestGenerator
{
    public function apply(ClassStub $classStub): bool
    {
        return str_ends_with($classStub->getFullQualifiedClassName(), 'Config\\Config');
    }

    public function generate(ClassStub $classStub, ClassStub $testClassStub): PhpGenerator
    {
        $phpGenerator = parent::generate($classStub, $testClassStub);
        $phpGenerator->addUse(ConfigFixture::class, 'ConfigFixture');

        foreach ($classStub->getClassMethods() as $classMethod) {
            if ($classMethod->isConstructor()) {
                continue;
            }

            $methodName = $classMethod->getName();
            $methodName = preg_replace('/^get/', '', $methodName);
            $path = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $methodName));

            $phpGenerator->addClassMethod(
                'test'.ucfirst($classMethod->getName()),
                $this->getTestConfigMethod($classStub, $classMethod)
            )->addAttribute(ConfigFixture::class, [
                'path' => $path,
                'value' => 'foobar',
            ]);
        }

        return $phpGenerator;
    }

    private function getTestConfigMethod(ClassStub $classStub, ReflectionMethod $method): string
    {
        $className = $classStub->getClassName();
        $methodName = $method->getName();

        return <<<EOF
\$config = \$this->om()->get({$className}::class);
\$this->assertSame('foobar', \$config->{$methodName}());
// @todo: Insert the correct configuration test
EOF;
    }
}
