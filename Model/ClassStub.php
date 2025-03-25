<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use ReflectionClass;
use ReflectionMethod;

class ClassStub
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
        private Filesystem $filesystem,
        private string $moduleName,
        private string $fullQualifiedClassName
    ) {
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getFullQualifiedClassName(): string
    {
        return trim($this->fullQualifiedClassName, '\\');
    }

    public function getModuleClassPrefix(): string
    {
        return str_replace('_', '\\', $this->moduleName);
    }

    public function getRelativeNamespace(): string
    {
        return str_replace($this->getModuleClassPrefix().'\\', '', $this->getNamespace());
    }

    public function getClassName(): string
    {
        return substr(strrchr($this->getFullQualifiedClassName(), '\\'), 1);
    }

    public function getNamespace(): string
    {
        $length = strlen($this->getFullQualifiedClassName()) - (strlen($this->getClassName()) + 1);

        return substr($this->getFullQualifiedClassName(), 0, $length);
    }

    public function getRelativePath(): string
    {
        $path = $this->getRelativeNamespace().'/'.$this->getClassName().'.php';

        return str_replace('\\', '/', $path);
    }

    public function getAbsolutePath(): string
    {
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $this->getModuleName());

        return $path.'/'.$this->getRelativePath();
    }

    /**
     * @return ReflectionMethod[]
     * @throws \ReflectionException
     */
    public function getClassMethods(): array
    {
        return $this->getReflection()->getMethods();
    }

    public function getInstance()
    {
        return ObjectManager::getInstance()->get($this->getFullQualifiedClassName());
    }

    public function getReflection(): ReflectionClass
    {
        return new ReflectionClass($this->getFullQualifiedClassName());
    }

    public function getContents(): string
    {
        $reader = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        return (string) $reader->readFile($this->getAbsolutePath());
    }
}
