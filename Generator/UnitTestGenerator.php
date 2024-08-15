<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
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

    public function generate(string $moduleName, OutputInterface $output, bool $overrideExisting)
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $testPath = $modulePath.'/Test/Unit/';
        if (false === $this->getWriter()->isExist($testPath)) {
            $this->getWriter()->create($testPath);
        }

        $classNames = $this->classCollector->collect($modulePath);
        foreach ($classNames as $className) {
            $output->writeln('Generating test for '.$className);
            $classStub = $this->classStubFactory->create($moduleName, $className);
            $testClassStub = $this->classStubFactory->createTest($classStub, 'Unit');
            $testContents = $this->genericTestGenerator->generate($classStub, $testClassStub);
            $testFile = $modulePath.'/'.$testClassStub->getRelativePath();
            $output->writeln('Writing file '.$testFile, OutputInterface::VERBOSITY_VERBOSE);
            if (true === $overrideExisting || false === $this->getWriter()->isExist($testFile)) {
                $this->getWriter()->writeFile($testFile, $testContents);
            }
        }
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
}
