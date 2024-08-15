<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Model;

use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Test\Unit\Stub\ObjectManagerStub;

class ClassStubFactoryTest extends TestCase
{
    public function testCreate()
    {
        $classStub = $this->factory()->create('Yireo_Foobar', '\Yireo\Foobar\Example');
        $this->assertEquals('Yireo_Foobar', $classStub->getModuleName());
    }

    public function testGetModuleName()
    {
        $classStub = $this->factory()->create('Yireo_Foobar', '\Yireo\Foobar\Example');
        $testClassStub = $this->factory()->createTest($classStub, 'unit');
        $this->assertEquals('Yireo_Foobar', $testClassStub->getModuleName());
    }

    public function testGetFullQualifiedClassName()
    {
        $classStub = $this->factory()->create('Yireo_TestGenerator', '\Yireo\TestGenerator\Some\Example');
        $testClassStub = $this->factory()->createTest($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Some\ExampleTest', $testClassStub->getFullQualifiedClassName());

        $classStub = $this->factory()->create('Yireo_TestGenerator', ClassStub::class);
        $testClassStub = $this->factory()->createTest($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Model\ClassStubTest', $testClassStub->getFullQualifiedClassName());
    }

    public function testGetModuleClassPrefix()
    {
        $classStub = $this->factory()->create('Yireo_Foobar', '\Yireo\Foobar\Some\Example');
        $testClassStub = $this->factory()->createTest($classStub, 'unit');
        $this->assertEquals('Yireo\Foobar', $testClassStub->getModuleClassPrefix());
    }

    public function testGetNamespace()
    {
        $classStub = $this->factory()->create('Yireo_TestGenerator', '\Yireo\TestGenerator\Some\Example');
        $testClassStub = $this->factory()->createTest($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Some', $testClassStub->getNamespace());

        $classStub = $this->factory()->create('Yireo_TestGenerator', ClassStub::class);
        $testClassStub = $this->factory()->createTest($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Model', $testClassStub->getNamespace());
    }

    public function testGetClassName()
    {
        $classStub = $this->factory()->create('Yireo_TestGenerator', '\Foo\Bar\Some\Example');
        $testClassStub = $this->factory()->createTest($classStub);
        $this->assertEquals('ExampleTest', $testClassStub->getClassName());
    }

    public function testGetRelativeNamespace()
    {
        $classStub = $this->factory()->create('Yireo_TestGenerator', ClassStub::class);
        $testClassStub = $this->factory()->createTest($classStub);
        $this->assertEquals('Test\\Integration\\Model', $testClassStub->getRelativeNamespace());
    }

    private function factory(): ClassStubFactory
    {
        $objectManager = new ObjectManagerStub();
        return new ClassStubFactory($objectManager);
    }
}
