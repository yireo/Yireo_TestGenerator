<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest;

use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegisteredForReal;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Generator\PhpGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;

class ModuleTestGenerator extends AbstractAdditionalTestGenerator
{
    public function apply(ModuleContext $moduleContext): bool
    {
        return true;
    }

    public function getTestStub(ModuleContext $moduleContext): ClassStub
    {
        return $this->createTestStub(
            $moduleContext->getModuleName(),
            $moduleContext->getTestNamespace().'\\ModuleTest'
        );
    }

    public function generate(ModuleContext $moduleContext): PhpGeneratorInterface
    {
        $testStub = $this->getTestStub($moduleContext);
        $phpGenerator = $this->getPhpGenerator($testStub);

        $phpGenerator->addTrait(AssertModuleIsEnabled::class);
        $phpGenerator->addTrait(AssertModuleIsRegistered::class);
        $phpGenerator->addTrait(AssertModuleIsRegisteredForReal::class);

        $phpGenerator->addClassMethod(
            'testModule',
            $this->getMethodModuleTest($moduleContext->getModuleName())
        );

        return $phpGenerator;
    }

    private function getMethodModuleTest(string $moduleName): string
    {
        return <<<EOF
\$moduleName = '$moduleName';
\$this->assertModuleIsEnabled(\$moduleName);
\$this->assertModuleIsRegistered(\$moduleName);
\$this->assertModuleIsRegisteredForReal(\$moduleName);
EOF;
    }
}
