<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use ReflectionClass;
use ReflectionParameter;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

class ObjectInstantiation
{
    public function create(PhpGenerator $phpGenerator, ClassStub $classStub): string
    {
        if ($this->instantiateClassViaGetObject($classStub)) {
            return $this->getTestInstantiationWithGetObject($phpGenerator, $classStub);
        }

        if ($this->instantiateClassViaGetCollectionMock($classStub)) {

            return $this->getTestInstantiationWithGetCollectionMock($phpGenerator, $classStub);

        }

        return $this->getTestInstantiationWithMocks($phpGenerator, $classStub);
    }

    private function getTestInstantiationWithGetObject(PhpGenerator $phpGenerator, ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $phpGenerator->addUse(ObjectManager::class);

        return <<<EOF
\$objectManager = new ObjectManager(\$this);
return \$objectManager->getObject({$className}::class);
EOF;
    }

    private function getTestInstantiationWithGetCollectionMock(PhpGenerator $phpGenerator, ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $phpGenerator->addUse(ObjectManager::class);

        return <<<EOF
\$objectManager = new ObjectManager(\$this);
return \$objectManager->getCollectionMock({$className}::class, []);
EOF;
    }

    private function getTestInstantiationWithMocks(PhpGenerator $phpGenerator, ClassStub $classStub): string
    {
        $className = $classStub->getClassName();
        $reflectionClass = new ReflectionClass($classStub->getFullQualifiedClassName());

        $test = '';
        $constructorArguments = [];
        if ($reflectionClass->getConstructor()) {
            foreach ($reflectionClass->getConstructor()->getParameters() as $reflectionParameter) {
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    continue;
                }

                $constructorArguments[] = '$'.$reflectionParameter->getName();
                $test .= $this->getCreateMockContents($phpGenerator, $reflectionParameter);
            }
        }

        $constructorArguments = implode(', ', $constructorArguments);
        $test .= <<<EOF
return new {$className}({$constructorArguments});
EOF;

        return $test;
    }

    private function getCreateMockContents(
        PhpGenerator $phpGenerator,
        ReflectionParameter $reflectionParameter,
    ): string {
        $parameterName = $reflectionParameter->getName();
        $parameterType = $reflectionParameter->getType();
        if (!$parameterType) {
            return "\$$parameterName = null;\n";
        }

        $parameterClass = '\\'.$parameterType->getName();
        if (false === interface_exists($parameterClass) && false === class_exists($parameterClass)) {
            return "\$$parameterName = (\$parameterType) null;\n";
        }

        $parameterClassName = basename(str_replace('\\', '/', $parameterClass));
        $parameterClassReflection = new ReflectionClass($parameterClass);
        $phpGenerator->addUse($parameterClass);

        if ($parameterClassReflection->isFinal()) {
            return "\$$parameterName = new $parameterClassName;\n";
        }

        return "\$$parameterName = \$this->createMock({$parameterClassName}::class);\n";
    }

    private function instantiateClassViaGetObject(ClassStub $classStub): bool
    {
        $classNames = [
            AbstractModel::class,
        ];

        foreach ($classNames as $className) {
            if (is_subclass_of($classStub->getFullQualifiedClassName(), $className)) {
                return true;
            }
        }

        return false;
    }

    private function instantiateClassViaGetCollectionMock(ClassStub $classStub): bool
    {
        $classNames = [
            AbstractCollection::class,
        ];

        foreach ($classNames as $className) {
            if (is_subclass_of($classStub->getFullQualifiedClassName(), $className)) {
                return true;
            }
        }

        return false;
    }
}
