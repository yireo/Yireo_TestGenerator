<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\GenericTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\ModuleTestGenerator;
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
        private ClassStubFactory $classStubFactory
    ) {
    }

    public function generate(string $moduleName, OutputInterface $output, bool $overrideExisting)
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $testPath = $modulePath.'/Test/Integration/';
        if (false === $this->getWriter()->isExist($testPath)) {
            $this->getWriter()->create($testPath);
        }

        $classNamePrefix = $this->getClassNamePrefix($moduleName);

        $testFile = $testPath.'ModuleTest.php';
        if (true === $overrideExisting || false === $this->getWriter()->isExist($testFile)) {
            $testContents = $this->moduleTestGenerator->generate($moduleName, $classNamePrefix);
            $output->writeln('Generating module test');
            $this->getWriter()->writeFile($testFile, $testContents);
        }

        $classNames = $this->classCollector->collect($modulePath);
        foreach ($classNames as $className) {

            $classStub = $this->classStubFactory->create($moduleName, $className);
            $testClassStub = $this->classStubFactory->createTest($classStub);
            $testFile = $modulePath.'/'.$testClassStub->getRelativePath();

            if (true === $overrideExisting || false === $this->getWriter()->isExist($testFile)) {
                $testContents = $this->genericTestGenerator->generate($classStub, $testClassStub);
                $output->writeln('Generating test for '.$className);
                $output->writeln('Writing file '.$testFile, OutputInterface::VERBOSITY_VERBOSE);
                $this->getWriter()->writeFile($testFile, $testContents);
            }
        }
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
}
