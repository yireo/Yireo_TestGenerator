<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Model\ClassStub;

class ClassStubTest extends TestCase
{
    public function testGetModuleName()
    {
        $classStub = new ClassStub('Yireo_Foobar', 'whatever');
        $this->assertEquals('Yireo_Foobar', $classStub->getModuleName());
    }

    public function testGetFullQualifiedClassName()
    {
        $classStub = new ClassStub('whatever', '\Yireo\Foobar\Some\Example');
        $this->assertEquals('Yireo\Foobar\Some\Example', $classStub->getFullQualifiedClassName());

        $classStub = new ClassStub('whatever', ClassStubTest::class);
        $this->assertEquals(ClassStubTest::class, $classStub->getFullQualifiedClassName());
    }

    public function testGetModuleClassPrefix()
    {
        $classStub = new ClassStub('Yireo_Foobar', '\Yireo\Foobar\Some\Example');
        $this->assertEquals('Yireo\Foobar', $classStub->getModuleClassPrefix());
    }

    public function testGetNamespace()
    {
        $classStub = new ClassStub('whatever', '\Foo\Bar\Some\Example');
        $this->assertEquals('Foo\Bar\Some', $classStub->getNamespace());

        $classStub = new ClassStub('whatever', ClassStubTest::class);
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Model', $classStub->getNamespace());
    }

    public function testGetClassName()
    {
        $classStub = new ClassStub('whatever', '\Foo\Bar\Some\Example');
        $this->assertEquals('Example', $classStub->getClassName());
    }

    public function testGetRelativeNamespace()
    {
        $classStub = new ClassStub('Yireo_TestGenerator', ClassStubTest::class);
        $this->assertEquals('Test\\Unit\\Model', $classStub->getRelativeNamespace());
    }
}
