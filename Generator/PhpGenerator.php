<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;

class PhpGenerator
{
    public function __construct(
        private ClassType $classType,
        private PhpNamespace $namespace,
        private PhpFile $file,
        private WriteInterface $writer
    ) {
    }

    public function addClassMethod(string $methodName, string $methodBody)
    {
        $this->classType->addMethod($methodName)
            ->setFinal()
            ->setPublic()
            ->setBody($methodBody);
    }

    public function addTrait(string $traitName)
    {
        $this->classType->addTrait($traitName);
        $this->addUse($traitName);
    }

    public function addUse(string $namespace)
    {
        $this->namespace->addUse($namespace);
    }

    public function generate(string $file):bool
    {
        $this->writer->writeFile($file, $this->output());
        return true;
    }

    public function output(): string
    {
        $this->namespace->add($this->classType);
        $this->file->addNamespace($this->namespace);
        return (new PsrPrinter)->printFile($this->file);
    }
}
