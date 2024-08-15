<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegisteredForReal;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;

class ModuleTestGenerator
{
    public function __construct(
        private PhpGeneratorFactory $phpGeneratorFactory,
    ) {
    }

    public function generate(string $moduleName, string $classNamePrefix): string
    {
        $phpGenerator = $this->phpGeneratorFactory->create('ModuleTest', $classNamePrefix);
        $phpGenerator->addTrait(AssertModuleIsEnabled::class);
        $phpGenerator->addTrait(AssertModuleIsRegistered::class);
        $phpGenerator->addTrait(AssertModuleIsRegisteredForReal::class);
        $phpGenerator->addClassMethod('testModule', $this->getMethodModuleTest($moduleName));

        return $phpGenerator->output();
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
