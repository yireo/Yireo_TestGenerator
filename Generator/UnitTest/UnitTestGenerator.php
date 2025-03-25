<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest;

use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\ModuleContextFactory;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Utilities\ClassCollector;

class UnitTestGenerator
{
    public function __construct(
        private ClassCollector $classCollector,
        private ClassStubFactory $classStubFactory,
        private ModuleContextFactory $moduleContextFactory,
        private SourceTestGeneratorListing $generatorListing
    ) {
    }

    public function generateAll(
        string $moduleName,
        OutputInterface $output,
        bool $overrideExisting
    ) {
        $moduleContext = $this->getModuleContext($moduleName);
        $classNames = $this->classCollector->collect($moduleContext->getPath());
        foreach ($classNames as $className) {
            $this->generateTestPerClass($moduleName, $className, $output, $overrideExisting);
        }
    }

    public function generateTestPerClass(
        string $moduleName,
        string $className,
        OutputInterface $output,
        bool $overrideExisting
    ) {
        $moduleContext = $this->getModuleContext($moduleName);

        $classStub = $this->classStubFactory->create($moduleName, $className);
        if ($classStub->getReflection()->isFinal() || $classStub->getReflection()->isAbstract()) {
            return;
        }

        $testClassStub = $this->classStubFactory->createTest($classStub, 'Unit');
        $testFile = $moduleContext->getPath().'/'.$testClassStub->getRelativePath();

        if (false === $overrideExisting && $moduleContext->getWriter()->isExist($testFile)) {
            $output->writeln('Test for '.$className.' already exists');

            return;
        }

        if ($moduleContext->getWriter()->isExist($testFile)) {
            $testContents = $moduleContext->getWriter()->readFile($testFile);
            if (str_contains($testContents, '@test-generator-skip-override')) {
                $output->writeln('Skipping override for '.$className);

                return;
            }
        }

        $testGenerator = $this->generatorListing->selectGenerator($classStub);
        $testContents = $testGenerator->generate($classStub, $testClassStub);
        if ($testContents instanceof PhpGenerator) {
            $testContents = $testContents->output();
        }

        $output->writeln('Writing '.$testClassStub->getRelativePath());
        $moduleContext->getWriter()->writeFile($testFile, $testContents);
    }

    private function getModuleContext(string $moduleName): ModuleContext
    {
        return $this->moduleContextFactory->create($moduleName, 'integration');
    }
}
