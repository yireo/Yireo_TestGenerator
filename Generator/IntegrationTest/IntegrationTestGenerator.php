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

    public function generateAll(
        string $moduleName,
        OutputInterface $output,
        bool $overrideExisting
    ) {
        $moduleContext = $this->getModuleContext($moduleName);

        foreach ($this->additionalTestGeneratorListing->getGenerators() as $testGenerator) {
            $testStub = $testGenerator->getTestStub($moduleContext);

            if (false === $overrideExisting || true === $moduleContext->getWriter()->isExist($testStub->getAbsolutePath())) {
                continue;
            }

            if (false === $testGenerator->apply($moduleContext)) {
                continue;
            }

            $testFile = $testStub->getAbsolutePath();
            $phpGenerator = $testGenerator->generate($moduleContext);

            $output->writeln('Writing '.$testStub->getRelativePath());
            $moduleContext->getWriter()->writeFile($testFile, $phpGenerator->output());
        }

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
