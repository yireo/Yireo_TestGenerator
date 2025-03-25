<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Model\ClassStub;

// @test-generator-skip-override
class ClassStubTest extends TestCase
{
    public function testGetModuleName()
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);
        $classStub = new ClassStub($componentRegistrar, $filesystem, 'Yireo_Foobar', 'whatever');
        $this->assertEquals('Yireo_Foobar', $classStub->getModuleName());
    }

    public function testGetFullQualifiedClassName()
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);

        $classStub = new ClassStub($componentRegistrar, $filesystem, 'whatever', '\Yireo\Foobar\Some\Example');
        $this->assertEquals('Yireo\Foobar\Some\Example', $classStub->getFullQualifiedClassName());

        $classStub = new ClassStub($componentRegistrar, $filesystem, 'whatever', ClassStubTest::class);
        $this->assertEquals(ClassStubTest::class, $classStub->getFullQualifiedClassName());
    }

    public function testGetModuleClassPrefix()
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);

        $classStub = new ClassStub($componentRegistrar, $filesystem, 'Yireo_Foobar', '\Yireo\Foobar\Some\Example');
        $this->assertEquals('Yireo\Foobar', $classStub->getModuleClassPrefix());
    }

    public function testGetNamespace()
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);

        $classStub = new ClassStub($componentRegistrar, $filesystem, 'whatever', '\Foo\Bar\Some\Example');
        $this->assertEquals('Foo\Bar\Some', $classStub->getNamespace());

        $classStub = new ClassStub($componentRegistrar, $filesystem, 'whatever', ClassStubTest::class);
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Model', $classStub->getNamespace());
    }

    public function testGetClassName()
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);

        $classStub = new ClassStub($componentRegistrar, $filesystem, 'whatever', '\Foo\Bar\Some\Example');
        $this->assertEquals('Example', $classStub->getClassName());
    }

    public function testGetRelativeNamespace()
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);

        $classStub = new ClassStub($componentRegistrar, $filesystem, 'Yireo_TestGenerator', ClassStubTest::class);
        $this->assertEquals('Test\\Unit\\Model', $classStub->getRelativeNamespace());
    }
}
