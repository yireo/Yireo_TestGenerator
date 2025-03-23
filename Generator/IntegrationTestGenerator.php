<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\ModuleTestGenerator;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Utilities\ClassCollector;

class IntegrationTestGenerator
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private ModuleTestGenerator $moduleTestGenerator,
        private DirectoryList $directoryList,
        private Filesystem $filesystem,
        private ClassCollector $classCollector,
        private ClassStubFactory $classStubFactory,
        private IntegrationTestGeneratorListing $generatorListing
    ) {
    }

    public function generateAll(
        string $moduleName,
        OutputInterface $output,
        bool $overrideExisting
    ) {
        $context = $this->getContext($moduleName);

        $testPath = $context->getModulePath().'/Test/Integration/';
        if (false === $this->getWriter()->isExist($testPath)) {
            $this->getWriter()->create($testPath);
        }

        $testFile = $testPath.'ModuleTest.php';
        if (true === $overrideExisting || false === $this->getWriter()->isExist($testFile)) {
            $phpGenerator = $this->moduleTestGenerator->generate($context);
            $output->writeln('Generating module test');
            $this->getWriter()->writeFile($testFile, $phpGenerator->output());
        }

        $classNames = $this->classCollector->collect($context->getModulePath());
        foreach ($classNames as $className) {
            $this->generateTest($moduleName, $className, $output, $overrideExisting);
        }
    }

    public function generateTest(
        string $moduleName,
        string $className,
        OutputInterface $output,
        bool $overrideExisting
    ): void {
        $context = $this->getContext($moduleName);

        $classStub = $this->classStubFactory->create($context->getModuleName(), $className);
        $testClassStub = $this->classStubFactory->createTest($classStub);
        $testFile = $context->getModulePath().'/'.$testClassStub->getRelativePath();

        if (false === $overrideExisting && $this->getWriter()->isExist($testFile)) {
            $output->writeln('Test for '.$className.' already exists');

            return;
        }

        $testGenerator = $this->generatorListing->selectGenerator($classStub);
        $output->writeln('Test generator: '.get_class($testGenerator));

        $phpGenerator = $testGenerator->generate($classStub, $testClassStub);
        $testContents = $phpGenerator->output();

        $output->writeln('Generating test for '.$className);
        $output->writeln('Writing file '.$testFile, OutputInterface::VERBOSITY_VERBOSE);
        $this->getWriter()->writeFile($testFile, $testContents);
    }

    private function getClassNamePrefix(string $moduleName): string
    {
        $moduleNameParts = explode('_', $moduleName);

        return $moduleNameParts[0].'\\'.$moduleNameParts[1].'\\Test\\Integration';
    }

    private function getWriter(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite($this->directoryList::ROOT);
    }

    private function getModulePath(string $moduleName): string
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (empty($modulePath)) {
            throw new RuntimeException('No path found for module "'.$moduleName.'"');
        }

        return $modulePath;
    }

    private function getContext(string $moduleName): Context
    {
        $modulePath = $this->getModulePath($moduleName);
        $classNamePrefix = $this->getClassNamePrefix($moduleName);
        return new Context($moduleName, $modulePath, $classNamePrefix);
    }
}
