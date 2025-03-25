<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\IntegrationTest\SourceTest;

use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\AbstractTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\GenericTestGenerator;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Model\ClassStub;

// @test-generator-skip-override
class GenericTestGeneratorTest extends TestCase
{
    public function testApply(): void
    {
        $classStub = $this->createMock(ClassStub::class);
        $phpGeneratorFactory = $this->createMock(PhpGeneratorFactory::class);

        $genericTestGenerator = new GenericTestGenerator($phpGeneratorFactory);
        $result = $genericTestGenerator->apply($classStub);
        
        $this->assertTrue($result);
    }
    
    public function testGenerate(): void
    {
        $classStub = $this->createMock(ClassStub::class);
        $classStub->method('getClassName')->willReturn('SomeClass');
        
        $testClassStub = $this->createMock(ClassStub::class);
        
        $parentPhpGenerator = $this->createMock(PhpGenerator::class);
        $parentPhpGenerator->method('addClassMethod')
            ->with('testIfInstantiationWorks', $this->isType('string'))
            ->willReturnSelf();
        
        $abstractTestGenerator = $this->createMock(AbstractTestGenerator::class);
        $abstractTestGenerator->method('generate')
            ->willReturn($parentPhpGenerator);
        
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);
        $result = $genericTestGenerator->generate($classStub, $testClassStub);
        
        $this->assertSame($parentPhpGenerator->output(), $result->output());
    }
}