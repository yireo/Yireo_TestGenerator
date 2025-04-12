<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use RuntimeException;

class ModuleContext
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private Filesystem $filesystem,
        private string $moduleName,
        private string $testType = 'integration',
    ) {
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getPath(): string
    {
        $moduleName = $this->getModuleName();
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (empty($modulePath)) {
            throw new RuntimeException('No path found for module "'.$moduleName.'"');
        }

        return $modulePath;
    }

    public function getTestPath(): string
    {
        $testPath = $this->getPath().'/Test/Integration/';
        if (false === $this->getWriter()->isExist($testPath)) {
            $this->getWriter()->create($testPath);
        }

        return $testPath;
    }

    public function getNamespace(): string
    {
        $moduleNameParts = explode('_', $this->getModuleName());

        return $moduleNameParts[0].'\\'.$moduleNameParts[1];
    }

    public function getTestNamespace(): string
    {
        return $this->getNamespace().'\\Test\\'.ucfirst($this->testType);
    }

    public function getWriter(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
    }
}
