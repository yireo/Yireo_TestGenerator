<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\UnitTest\GenericTestGenerator;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Utilities\ClassCollector;

class UnitTestGenerator
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private GenericTestGenerator $genericTestGenerator,
        private DirectoryList $directoryList,
        private Filesystem $filesystem,
        private ClassCollector $classCollector,
        private ClassStubFactory $classStubFactory
    ) {
    }

    public function generateAll(
        string $moduleName,
        OutputInterface $output,
        bool $overrideExisting
    ) {
        $modulePath = $this->getModulePath($moduleName);
        $testPath = $modulePath.'/Test/Unit/';
        if (false === $this->getWriter()->isExist($testPath)) {
            $this->getWriter()->create($testPath);
        }

        $classNames = $this->classCollector->collect($modulePath);
        foreach ($classNames as $className) {
            $this->generateTest($moduleName, $className, $output, $overrideExisting);
        }
    }

    public function generateTest(
        string $moduleName,
        string $className,
        OutputInterface $output,
        bool $overrideExisting
    ) {
        $output->writeln('Generating test for '.$className);

        $modulePath = $this->getModulePath($moduleName);
        $classStub = $this->classStubFactory->create($moduleName, $className);
        $testClassStub = $this->classStubFactory->createTest($classStub, 'Unit');
        $testContents = $this->genericTestGenerator->generate($classStub, $testClassStub);
        $testFile = $modulePath.'/'.$testClassStub->getRelativePath();

        if (false === $overrideExisting && $this->getWriter()->isExist($testFile)) {
            $output->writeln('Test for '.$className.' already exists');

            return;
        }

        $output->writeln('Writing file '.$testFile, OutputInterface::VERBOSITY_VERBOSE);

        $this->getWriter()->writeFile($testFile, $testContents);
    }

    private function getClassNamePrefix(string $moduleName): string
    {
        $moduleNameParts = explode('_', $moduleName);

        return $moduleNameParts[0].'\\'.$moduleNameParts[1].'\\Test\\Unit';
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
}
