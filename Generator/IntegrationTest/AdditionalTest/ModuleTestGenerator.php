<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsEnabled;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\AssertModuleIsRegistered;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Model\ClassStubFactory;

class ModuleTestGenerator extends AbstractAdditionalTestGenerator
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        PhpGeneratorFactory $phpGeneratorFactory,
        ClassStubFactory $classStubFactory
    ) {
        parent::__construct($phpGeneratorFactory, $classStubFactory);
    }

    public function apply(ModuleContext $moduleContext, bool $overrideExisting): bool
    {
        if ($overrideExisting) {
            return true;
        }

        if (false === $moduleContext->getWriter()->isExist($this->getTestFile($moduleContext))) {
            return true;
        }

        return false;
    }

    public function generate(ModuleContext $moduleContext, OutputInterface $output): bool
    {
        $testStub = $this->createTestStub(
            $moduleContext->getModuleName(),
            $moduleContext->getTestNamespace().'\\ModuleTest'
        );

        $phpGenerator = $this->getPhpGenerator($testStub);

        $phpGenerator->addTrait(AssertModuleIsEnabled::class);
        $phpGenerator->addTrait(AssertModuleIsRegistered::class);

        $phpGenerator->addClassMethod(
            'testModule',
            $this->getMethodModuleTest($moduleContext->getModuleName())
        );


        $testFile = $this->getTestFile($moduleContext);
        $moduleContext->getWriter()->writeFile($testFile, $phpGenerator->output());

        return true;
    }

    private function getMethodModuleTest(string $moduleName): string
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $moduleFile = $modulePath . '/etc/module.xml';
        $xml = simplexml_load_file($moduleFile);

        $moduleStrings = "'".$moduleName."',\n";
        foreach ($xml->module->sequence->children() as $module) {
            $moduleStrings .= "'".(string)$module['name']."',\n";
        }

        return <<<EOF
\$moduleNames = [
{$moduleStrings}
];

foreach (\$moduleNames as \$moduleName) {
    \$this->assertModuleIsEnabled(\$moduleName);
    \$this->assertModuleIsRegistered(\$moduleName);
}
EOF;
    }

    private function getTestFile(ModuleContext $moduleContext): string
    {
        return rtrim($moduleContext->getTestPath(), '/').'/ModuleTest.php';
    }
}
