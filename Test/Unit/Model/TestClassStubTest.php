<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Model\ClassStub;

class TestClassStubTest extends TestCase
{
    public function testGetModuleName()
    {
        $classStub = new ClassStub('Yireo_Foobar', '\Yireo\Foobar\Example');
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub, 'unit');
        $this->assertEquals('Yireo_Foobar', $testClassStub->getModuleName());
    }

    public function testGetFullQualifiedClassName()
    {
        $classStub = new ClassStub('Yireo_TestGenerator', '\Yireo\TestGenerator\Some\Example');
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Some\ExampleTest', $testClassStub->getFullQualifiedClassName());

        $classStub = new ClassStub('Yireo_TestGenerator', ClassStub::class);
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Generator\ClassStubTest', $testClassStub->getFullQualifiedClassName());
    }

    public function testGetModuleClassPrefix()
    {
        $classStub = new ClassStub('Yireo_Foobar', '\Yireo\Foobar\Some\Example');
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub, 'unit');
        $this->assertEquals('Yireo\Foobar', $testClassStub->getModuleClassPrefix());
    }

    public function testGetNamespace()
    {
        $classStub = new ClassStub('Yireo_TestGenerator', '\Yireo\TestGenerator\Some\Example');
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Some', $testClassStub->getNamespace());

        $classStub = new ClassStub('Yireo_TestGenerator', ClassStub::class);
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub, 'unit');
        $this->assertEquals('Yireo\TestGenerator\Test\Unit\Generator', $testClassStub->getNamespace());
    }

    public function testGetClassName()
    {
        $classStub = new ClassStub('Yireo_TestGenerator', '\Foo\Bar\Some\Example');
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub);
        $this->assertEquals('ExampleTest', $testClassStub->getClassName());
    }

    public function testGetRelativeNamespace()
    {
        $classStub = new ClassStub('Yireo_TestGenerator', ClassStub::class);
        $testClassStub = TestClassStub::generateFromOriginalClassStub($classStub);
        $this->assertEquals('Test\\Integration\\Generator', $testClassStub->getRelativeNamespace());
    }
}
