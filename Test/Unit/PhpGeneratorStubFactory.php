<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Yireo\TestGenerator\Generator\PhpGenerator;

class PhpGeneratorStubFactory
{
    public function create(
        string $className,
        string $namespace,
        WriteInterface $writer
    ): PhpGenerator {
        $classType = new ClassType($className);
        $phpNamespace = new PhpNamespace($namespace);
        $phpFile = new PhpFile();
        return new PhpGenerator($classType, $phpNamespace, $phpFile, $writer);
    }
}
