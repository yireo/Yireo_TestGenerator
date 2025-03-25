<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\GenericTestGenerator;

use Magento\Framework\App\ObjectManager;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

class ObjectInstantiation
{
    public function create(PhpGenerator $phpGenerator, ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $phpGenerator->addUse(ObjectManager::class);

        return <<<EOF
\$objectManager = ObjectManager::getInstance();
return \$objectManager->create({$className}::class);
EOF;
    }
}
