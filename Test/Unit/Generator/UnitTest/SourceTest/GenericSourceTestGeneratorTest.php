<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\UnitTest\SourceTest;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator;
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

        $phpGeneratorFactory = $this->createMock(PhpGeneratorFactory::class);
        $generator = new GenericSourceTestGenerator($phpGeneratorFactory);

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

        $generator = new GenericSourceTestGenerator($phpGeneratorFactory);
        $result = $generator->generate($classStub, $testClassStub);
        
        $this->assertSame($phpGenerator, $result);
    }
}