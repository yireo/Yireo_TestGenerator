<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\UnitTest\SourceTest;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\ObjectInstantiation;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\TestInstantiationMethod;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\TestMethodCallMethod;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator\TestMockingMethod;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Test\Unit\PhpGeneratorStubFactory;

class GenericSourceTestGeneratorTest extends TestCase
{
    /**
     * Test the apply method
     */
    public function testApply(): void
    {
        $classStub = $this->createMock(ClassStub::class);

        $generator = new GenericSourceTestGenerator(
            $this->createMock(ObjectInstantiation::class),
            $this->createMock(TestMockingMethod::class),
            $this->createMock(TestInstantiationMethod::class),
            $this->createMock(TestMethodCallMethod::class),
            $this->createMock(PhpGeneratorFactory::class)
        );

        $result = $generator->apply($classStub);

        $this->assertTrue($result);
    }

    /**
     * Test the generate method
     */
    public function testGenerate(): void
    {
        $classStub = $this->createMock(ClassStub::class);
        $classStub->method('getClassName')->willReturn('TestClass');
        $classStub->method('getFullQualifiedClassName')->willReturn(__CLASS__);

        $testClassStub = $this->createMock(ClassStub::class);

        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'TestClass',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );

        $phpGeneratorFactory = $this->createMock(PhpGeneratorFactory::class);
        $phpGeneratorFactory->method('create')->willReturn($phpGenerator);

        $objectInstantiation = $this->createMock(ObjectInstantiation::class);
        $objectInstantiation->method('create')->willReturn('return new TestClass();');

        $testMockingMethod = $this->createMock(TestMockingMethod::class);
        $testMockingMethod->method('get')->willReturn('// mocking');

        $testInstantiationMethod = $this->createMock(TestInstantiationMethod::class);
        $testInstantiationMethod->method('get')->willReturn('// instantiation');

        $testMethodCallMethod = $this->createMock(TestMethodCallMethod::class);
        $testMethodCallMethod->method('get')->willReturn('// method call');

        $generator = new GenericSourceTestGenerator(
            $objectInstantiation,
            $testMockingMethod,
            $testInstantiationMethod,
            $testMethodCallMethod,
            $phpGeneratorFactory
        );
        $result = $generator->generate($classStub, $testClassStub);

        $this->assertSame($phpGenerator, $result);

        $classType = $phpGenerator->getClassType();
        $this->assertTrue($classType->hasMethod('testMocking'));
        $this->assertTrue($classType->hasMethod('testInstantiation'));
        $this->assertTrue($classType->hasMethod('testTestApply'));
        $this->assertTrue($classType->hasMethod('getInstance'));
    }
}
