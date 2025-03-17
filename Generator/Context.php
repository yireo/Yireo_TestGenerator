<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

class Context
{
    public function __construct(
        private string $moduleName,
        private string $modulePath,
        private string $classNamePrefix,
    ) {
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getModulePath(): string
    {
        return $this->modulePath;
    }

    public function getClassNamePrefix(): string
    {
        return $this->classNamePrefix;
    }
}
