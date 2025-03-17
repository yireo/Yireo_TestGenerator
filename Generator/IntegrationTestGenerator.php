<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\AbstractTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\GenericTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\ModuleTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\TestGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Utilities\ClassCollector;

class IntegrationTestGenerator
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private ModuleTestGenerator $moduleTestGenerator,
        private GenericTestGenerator $genericTestGenerator,
        private DirectoryList $directoryList,
        private Filesystem $filesystem,
        private ClassCollector $classCollector,
        private ClassStubFactory $classStubFactory,
        private array $testGenerators = [],
    ) {
    }

    public function generateAll(
        string $moduleName,
        OutputInterface $output,
        bool $overrideExisting
    ) {
        $modulePath = $this->getModulePath($moduleName);
        $testPath = $modulePath.'/Test/Integration/';
        if (false === $this->getWriter()->isExist($testPath)) {
            $this->getWriter()->create($testPath);
        }

        $classNamePrefix = $this->getClassNamePrefix($moduleName);

        $testFile = $testPath.'ModuleTest.php';
        if (true === $overrideExisting || false === $this->getWriter()->isExist($testFile)) {
            $phpGenerator = $this->moduleTestGenerator->generate($moduleName, $classNamePrefix);
            $testContents = $phpGenerator->output();
            $output->writeln('Generating module test');
            $this->getWriter()->writeFile($testFile, $testContents);
        }

        $classNames = $this->classCollector->collect($modulePath);
        foreach ($classNames as $className) {
            $this->generateTest($className, $modulePath, $output, $overrideExisting);
        }
    }

    public function generateTest(
        string $moduleName,
        string $className,
        OutputInterface $output,
        bool $overrideExisting
    ): void {
        $modulePath = $this->getModulePath($moduleName);

        $classStub = $this->classStubFactory->create($moduleName, $className);
        $testClassStub = $this->classStubFactory->createTest($classStub);
        $testFile = $modulePath.'/'.$testClassStub->getRelativePath();

        if (false === $overrideExisting && $this->getWriter()->isExist($testFile)) {
            $output->writeln('Test for '.$className.' already exists');

            return;
        }

        $testGenerator = $this->getTestGenerator($classStub);
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

    private function getTestGenerator(ClassStub $classStub): TestGeneratorInterface
    {
        foreach ($this->testGenerators as $testGenerator) {
            if (false === $testGenerator instanceof TestGeneratorInterface) {
                continue;
            }

            if ($testGenerator->apply($classStub)) {
                return $testGenerator;
            }
        }

        return $this->genericTestGenerator;
    }
}
