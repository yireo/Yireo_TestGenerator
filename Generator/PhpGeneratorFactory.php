<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Model\ClassStub;

class PhpGeneratorFactory
{
    public function __construct(
        private DirectoryList $directoryList,
        private Filesystem $filesystem
    ) {
    }

    public function create(ClassStub $classStub): PhpGeneratorInterface
    {
        $className = $classStub->getClassName();
        $classNamespace = $classStub->getNamespace();

        $classType = new ClassType($className);
        $classType->setFinal();
        $classType->setExtends(TestCase::class);

        $namespaceType = new PhpNamespace($classNamespace);
        $namespaceType->add($classType);
        $namespaceType->addUse(TestCase::class);

        $fileType = new PhpFile;
        $fileType->setStrictTypes();

        $writer = $this->filesystem->getDirectoryWrite($this->directoryList::ROOT);
        
        return new PhpGenerator($classType, $namespaceType, $fileType, $writer);
    }
}
