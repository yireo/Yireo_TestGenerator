<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use ReflectionClass;
use ReflectionMethod;

class ClassStub
{
    public function __construct(
        private ComponentRegistrar $componentRegistrar,
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
        $reflectionClass = new ReflectionClass($this->getFullQualifiedClassName());

        return $reflectionClass->getMethods();
    }

    public function getInstance()
    {
        return ObjectManager::getInstance()->get($this->getFullQualifiedClassName());
    }
}
