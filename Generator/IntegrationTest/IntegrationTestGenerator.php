<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\ModuleContextFactory;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Utilities\ClassCollector;

class IntegrationTestGenerator
{
    public function __construct(
        private ClassCollector $classCollector,
        private ClassStubFactory $classStubFactory,
        private ModuleContextFactory $moduleContextFactory,
        private SourceTestGeneratorListing $sourceTestGeneratorListing,
        private AdditionalTestGeneratorListing $additionalTestGeneratorListing
    ) {
    }

    public function generateAdditionalTests(
        string $moduleName,
        OutputInterface $output,
        bool $overrideExisting,
    ): void {
        $moduleContext = $this->getModuleContext($moduleName);

        foreach ($this->additionalTestGeneratorListing->getGenerators() as $testGenerator) {
            if (false === $testGenerator->apply($moduleContext, $overrideExisting)) {
                continue;
            }

            $output->writeln('Calling '.get_class($testGenerator));
            $testGenerator->generate($moduleContext, $output);
        }
    }

    public function generateSourceTests(
        string $moduleName,
        OutputInterface $output,
        bool $overrideExisting,
    ): void {
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
    ): void {
        $moduleContext = $this->getModuleContext($moduleName);

        $classStub = $this->classStubFactory->create($moduleContext->getModuleName(), $className);
        $testClassStub = $this->classStubFactory->createTest($classStub);
        $testFile = $moduleContext->getPath().'/'.$testClassStub->getRelativePath();

        if (false === $overrideExisting && $moduleContext->getWriter()->isExist($testFile)) {
            $output->writeln('Test for '.$className.' already exists');

            return;
        }

        $testGenerator = $this->sourceTestGeneratorListing->selectGenerator($classStub);
        $phpGenerator = $testGenerator->generate($classStub, $testClassStub);
        $testContents = $phpGenerator->output();

        $output->writeln('Writing '.$testClassStub->getRelativePath());
        $moduleContext->getWriter()->writeFile($testFile, $testContents);
    }

    private function getModuleContext(string $moduleName): ModuleContext
    {
        return $this->moduleContextFactory->create($moduleName, 'integration');
    }
}
