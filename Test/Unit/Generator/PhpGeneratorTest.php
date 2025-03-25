<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Test\Unit\PhpGeneratorStubFactory;

class PhpGeneratorTest extends TestCase
{
    public function testGetClassType(): void
    {
        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );

        $classType = new ClassType('Dummy');
        $this->assertSame($classType->getName(), $phpGenerator->getClassType()->getName());
    }

    public function testAddClassMethod(): void
    {
        $methodName = 'testMethod';
        $methodBody = 'return true;';
        $method = new Method($methodName);
        $method->setBody($methodBody);

        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );
        $phpGenerator->addClassMethod($methodName, $methodBody);

        $this->assertStringContainsString('function testMethod()', $phpGenerator->output());
    }

    public function testAddTrait(): void
    {
        $traitName = 'Some\\Namespace\\TraitName';

        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );
        $phpGenerator->addTrait($traitName);

        $this->assertStringContainsString('use Some\\Namespace\\TraitName;', $phpGenerator->output());
    }

    public function testAddUseWithoutAlias(): void
    {
        $namespaceName = 'Some\\Namespace\\ClassName';

        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );
        $phpGenerator->addUse($namespaceName);

        $this->assertStringContainsString('use Some\\Namespace\\ClassName;', $phpGenerator->output());
    }

    public function testAddUseWithAlias(): void
    {
        $namespaceName = 'Some\\Namespace\\ClassName';
        $alias = 'AliasName';

        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );
        $phpGenerator->addUse($namespaceName, $alias);

        $this->assertStringContainsString('use Some\\Namespace\\ClassName as AliasName;', $phpGenerator->output());
    }

    public function testAddConstant(): void
    {
        $name = 'CONSTANT_NAME';
        $value = 'constant_value';

        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );
        $phpGenerator->addConstant($name, $value);

        $this->assertStringContainsString('const CONSTANT_NAME', $phpGenerator->output());

    }

    public function testOutput(): void
    {
        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );

        $this->assertStringContainsString('class Dummy', $phpGenerator->output());
    }
}
