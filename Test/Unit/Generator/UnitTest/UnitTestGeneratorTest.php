<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\UnitTest;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\ModuleContextFactory;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\SourceTestGeneratorInterface;
use Yireo\TestGenerator\Generator\UnitTest\SourceTestGeneratorListing;
use Yireo\TestGenerator\Generator\UnitTest\UnitTestGenerator;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Utilities\ClassCollector;

// @test-generator-skip-override
class UnitTestGeneratorTest extends TestCase
{
    public function testGenerateAll(): void
    {
        $moduleName = 'TestModule';
        $classNames = ['TestClass1', 'TestClass2'];
        $outputMock = $this->createMock(OutputInterface::class);
        $overrideExisting = false;

        $classCollectorMock = $this->createMock(ClassCollector::class);
        $classCollectorMock->expects($this->once())
            ->method('collect')
            ->willReturn($classNames);

        $testStubMock = $this->createMock(ClassStub::class);
        $classStubFactoryMock = $this->createMock(ClassStubFactory::class);
        $classStubFactoryMock->method('createTest')->willReturn($testStubMock);

        $writerMock = $this->createMock(WriteInterface::class);

        $moduleContextMock = $this->createMock(ModuleContext::class);
        $moduleContextMock->method('getPath')->willReturn('/path/to/module');
        $moduleContextMock->method('getWriter')->willReturn($writerMock);

        $moduleContextFactoryMock = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactoryMock->method('create')
            ->with($moduleName, 'integration')
            ->willReturn($moduleContextMock);

        $generatorListingMock = $this->createMock(SourceTestGeneratorListing::class);

        $unitTestGenerator = new UnitTestGenerator(
            $classCollectorMock,
            $classStubFactoryMock,
            $moduleContextFactoryMock,
            $generatorListingMock
        );

        $unitTestGenerator->generateAll($moduleName, $outputMock, $overrideExisting);
    }

    public function testGenerateTestPerClassSkipsWithFinalClass(): void
    {
        $moduleName = 'TestModule';
        $className = 'TestClass';
        $outputMock = $this->createMock(OutputInterface::class);
        $overrideExisting = false;

        $moduleContextMock = $this->createMock(ModuleContext::class);

        $moduleContextFactoryMock = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactoryMock->method('create')
            ->with($moduleName, 'integration')
            ->willReturn($moduleContextMock);

        $reflectionMock = $this->createMock(\ReflectionClass::class);
        $reflectionMock->method('isFinal')->willReturn(true);
        $reflectionMock->method('isAbstract')->willReturn(false);

        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getReflection')->willReturn($reflectionMock);

        $classStubFactoryMock = $this->createMock(ClassStubFactory::class);
        $classStubFactoryMock->method('create')
            ->with($moduleName, $className)
            ->willReturn($classStubMock);

        $classCollectorMock = $this->createMock(ClassCollector::class);
        $generatorListingMock = $this->createMock(SourceTestGeneratorListing::class);

        $unitTestGenerator = new UnitTestGenerator(
            $classCollectorMock,
            $classStubFactoryMock,
            $moduleContextFactoryMock,
            $generatorListingMock
        );

        $unitTestGenerator->generateTestPerClass($moduleName, $className, $outputMock, $overrideExisting);
    }

    public function testGenerateTestPerClassSkipsWithAbstractClass(): void
    {
        $moduleName = 'TestModule';
        $className = 'TestClass';
        $outputMock = $this->createMock(OutputInterface::class);
        $overrideExisting = false;

        $moduleContextMock = $this->createMock(ModuleContext::class);

        $moduleContextFactoryMock = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactoryMock->method('create')
            ->with($moduleName, 'integration')
            ->willReturn($moduleContextMock);

        $reflectionMock = $this->createMock(\ReflectionClass::class);
        $reflectionMock->method('isFinal')->willReturn(false);
        $reflectionMock->method('isAbstract')->willReturn(true);

        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getReflection')->willReturn($reflectionMock);

        $classStubFactoryMock = $this->createMock(ClassStubFactory::class);
        $classStubFactoryMock->method('create')
            ->with($moduleName, $className)
            ->willReturn($classStubMock);

        $classCollectorMock = $this->createMock(ClassCollector::class);
        $generatorListingMock = $this->createMock(SourceTestGeneratorListing::class);

        $unitTestGenerator = new UnitTestGenerator(
            $classCollectorMock,
            $classStubFactoryMock,
            $moduleContextFactoryMock,
            $generatorListingMock
        );

        $unitTestGenerator->generateTestPerClass($moduleName, $className, $outputMock, $overrideExisting);
    }

    public function testGenerateTestPerClassSkipsWhenTestExistsAndNotOverriding(): void
    {
        $moduleName = 'TestModule';
        $className = 'TestClass';
        $outputMock = $this->createMock(OutputInterface::class);
        $overrideExisting = false;
        $testRelativePath = 'Test/Unit/TestClassTest.php';

        $writerMock = $this->createMock(WriteInterface::class);
        $writerMock->method('isExist')->willReturn(true);

        $moduleContextMock = $this->createMock(ModuleContext::class);
        $moduleContextMock->method('getPath')->willReturn('/path/to/module');
        $moduleContextMock->method('getWriter')->willReturn($writerMock);

        $moduleContextFactoryMock = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactoryMock->method('create')
            ->with($moduleName, 'integration')
            ->willReturn($moduleContextMock);

        $reflectionMock = $this->createMock(\ReflectionClass::class);
        $reflectionMock->method('isFinal')->willReturn(false);
        $reflectionMock->method('isAbstract')->willReturn(false);

        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getReflection')->willReturn($reflectionMock);

        $testClassStubMock = $this->createMock(ClassStub::class);
        $testClassStubMock->method('getRelativePath')->willReturn($testRelativePath);

        $classStubFactoryMock = $this->createMock(ClassStubFactory::class);
        $classStubFactoryMock->method('create')
            ->with($moduleName, $className)
            ->willReturn($classStubMock);
        $classStubFactoryMock->method('createTest')
            ->with($classStubMock, 'Unit')
            ->willReturn($testClassStubMock);

        $outputMock->expects($this->once())
            ->method('writeln')
            ->with('Test for ' . $className . ' already exists');

        $classCollectorMock = $this->createMock(ClassCollector::class);
        $generatorListingMock = $this->createMock(SourceTestGeneratorListing::class);

        $unitTestGenerator = new UnitTestGenerator(
            $classCollectorMock,
            $classStubFactoryMock,
            $moduleContextFactoryMock,
            $generatorListingMock
        );

        $unitTestGenerator->generateTestPerClass($moduleName, $className, $outputMock, $overrideExisting);
    }

    public function testGenerateTestPerClassSkipsWhenTestHasSkipOverrideAnnotation(): void
    {
        $moduleName = 'TestModule';
        $className = 'TestClass';
        $outputMock = $this->createMock(OutputInterface::class);
        $overrideExisting = true;
        $testRelativePath = 'Test/Unit/TestClassTest.php';
        $testContents = '<?php /** @test-generator-skip-override */ class Test {}';

        $writerMock = $this->createMock(WriteInterface::class);
        $writerMock->method('isExist')->willReturn(true);
        $writerMock->method('readFile')->willReturn($testContents);

        $moduleContextMock = $this->createMock(ModuleContext::class);
        $moduleContextMock->method('getPath')->willReturn('/path/to/module');
        $moduleContextMock->method('getWriter')->willReturn($writerMock);

        $moduleContextFactoryMock = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactoryMock->method('create')
            ->with($moduleName, 'integration')
            ->willReturn($moduleContextMock);

        $reflectionMock = $this->createMock(\ReflectionClass::class);
        $reflectionMock->method('isFinal')->willReturn(false);
        $reflectionMock->method('isAbstract')->willReturn(false);

        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getReflection')->willReturn($reflectionMock);

        $testClassStubMock = $this->createMock(ClassStub::class);
        $testClassStubMock->method('getRelativePath')->willReturn($testRelativePath);

        $classStubFactoryMock = $this->createMock(ClassStubFactory::class);
        $classStubFactoryMock->method('create')
            ->with($moduleName, $className)
            ->willReturn($classStubMock);
        $classStubFactoryMock->method('createTest')
            ->with($classStubMock, 'Unit')
            ->willReturn($testClassStubMock);

        $outputMock->expects($this->once())
            ->method('writeln')
            ->with('Skipping override for ' . $className);

        $classCollectorMock = $this->createMock(ClassCollector::class);
        $generatorListingMock = $this->createMock(SourceTestGeneratorListing::class);

        $unitTestGenerator = new UnitTestGenerator(
            $classCollectorMock,
            $classStubFactoryMock,
            $moduleContextFactoryMock,
            $generatorListingMock
        );

        $unitTestGenerator->generateTestPerClass($moduleName, $className, $outputMock, $overrideExisting);
    }

    public function testGenerateTestPerClassWithStringGenerator(): void
    {
        $moduleName = 'TestModule';
        $className = 'TestClass';
        $outputMock = $this->createMock(OutputInterface::class);

        $overrideExisting = true;
        $testRelativePath = 'Test/Unit/TestClassTest.php';
        $testContents = '<?php class Test {}';

        $writerMock = $this->createMock(WriteInterface::class);
        $writerMock->method('isExist')->willReturn(false);

        $moduleContextMock = $this->createMock(ModuleContext::class);
        $moduleContextMock->method('getPath')->willReturn('/path/to/module');
        $moduleContextMock->method('getWriter')->willReturn($writerMock);

        $moduleContextFactoryMock = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactoryMock->method('create')
            ->with($moduleName, 'integration')
            ->willReturn($moduleContextMock);

        $reflectionMock = $this->createMock(\ReflectionClass::class);
        $reflectionMock->method('isFinal')->willReturn(false);
        $reflectionMock->method('isAbstract')->willReturn(false);

        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getReflection')->willReturn($reflectionMock);

        $testClassStubMock = $this->createMock(ClassStub::class);
        $testClassStubMock->method('getRelativePath')->willReturn($testRelativePath);

        $classStubFactoryMock = $this->createMock(ClassStubFactory::class);
        $classStubFactoryMock->method('create')
            ->with($moduleName, $className)
            ->willReturn($classStubMock);
        $classStubFactoryMock->method('createTest')
            ->with($classStubMock, 'Unit')
            ->willReturn($testClassStubMock);

        $testGeneratorMock = $this->createMock(SourceTestGeneratorInterface::class);

        $testGeneratorMock->method('generate')
            ->with($classStubMock, $testClassStubMock)
            ->willReturn($testContents);

        $generatorListingMock = $this->createMock(SourceTestGeneratorListing::class);
        $generatorListingMock->method('selectGenerator')
            ->with($classStubMock)
            ->willReturn($testGeneratorMock);

        $outputMock->expects($this->once())
            ->method('writeln')
            ->with('Writing ' . $testRelativePath);

        $writerMock->expects($this->once())
            ->method('writeFile')
            ->with('/path/to/module/' . $testRelativePath, $testContents);

        $classCollectorMock = $this->createMock(ClassCollector::class);

        $unitTestGenerator = new UnitTestGenerator(
            $classCollectorMock,
            $classStubFactoryMock,
            $moduleContextFactoryMock,
            $generatorListingMock
        );

        $unitTestGenerator->generateTestPerClass($moduleName, $className, $outputMock, $overrideExisting);
    }

    public function testGenerateTestPerClassWithPhpGenerator(): void
    {
        $moduleName = 'TestModule';
        $className = 'TestClass';
        $outputMock = $this->createMock(OutputInterface::class);
        $overrideExisting = true;
        $testRelativePath = 'Test/Unit/TestClassTest.php';
        $generatedContent = '<?php class Test {}';

        $writerMock = $this->createMock(WriteInterface::class);
        $writerMock->method('isExist')->willReturn(false);

        $moduleContextMock = $this->createMock(ModuleContext::class);
        $moduleContextMock->method('getPath')->willReturn('/path/to/module');
        $moduleContextMock->method('getWriter')->willReturn($writerMock);

        $moduleContextFactoryMock = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactoryMock->method('create')
            ->with($moduleName, 'integration')
            ->willReturn($moduleContextMock);

        $reflectionMock = $this->createMock(\ReflectionClass::class);
        $reflectionMock->method('isFinal')->willReturn(false);
        $reflectionMock->method('isAbstract')->willReturn(false);

        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getReflection')->willReturn($reflectionMock);

        $testClassStubMock = $this->createMock(ClassStub::class);
        $testClassStubMock->method('getRelativePath')->willReturn($testRelativePath);

        $classStubFactoryMock = $this->createMock(ClassStubFactory::class);
        $classStubFactoryMock->method('create')
            ->with($moduleName, $className)
            ->willReturn($classStubMock);
        $classStubFactoryMock->method('createTest')
            ->with($classStubMock, 'Unit')
            ->willReturn($testClassStubMock);

        $phpGeneratorMock = $this->createMock(PhpGenerator::class);
        $phpGeneratorMock->method('output')->willReturn($generatedContent);

        $testGeneratorMock = $this->createMock(SourceTestGeneratorInterface::class);
        $testGeneratorMock->method('generate')
            ->with($classStubMock, $testClassStubMock)
            ->willReturn($phpGeneratorMock);

        $generatorListingMock = $this->createMock(SourceTestGeneratorListing::class);
        $generatorListingMock->method('selectGenerator')
            ->with($classStubMock)
            ->willReturn($testGeneratorMock);

        $outputMock->expects($this->once())
            ->method('writeln')
            ->with('Writing ' . $testRelativePath);

        $writerMock->expects($this->once())
            ->method('writeFile')
            ->with('/path/to/module/' . $testRelativePath, $generatedContent);

        $classCollectorMock = $this->createMock(ClassCollector::class);

        $unitTestGenerator = new UnitTestGenerator(
            $classCollectorMock,
            $classStubFactoryMock,
            $moduleContextFactoryMock,
            $generatorListingMock
        );

        $unitTestGenerator->generateTestPerClass($moduleName, $className, $outputMock, $overrideExisting);
    }
}
