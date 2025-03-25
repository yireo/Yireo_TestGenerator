<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\IntegrationTest\AdditionalTest;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest\ModuleTestGenerator;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\PhpGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Model\ClassStubFactory;

// @test-generator-skip-override
class ModuleTestGeneratorTest extends TestCase
{
    public function testApply(): void
    {
        $moduleContext = $this->createMock(ModuleContext::class);

        $phpGeneratorFactory = new PhpGeneratorFactory(
            $this->createMock(DirectoryList::class),
            $this->createMock(Filesystem::class),
        );

        $classStubFactory = $this->createMock(ClassStubFactory::class);

        $generator = new ModuleTestGenerator($phpGeneratorFactory, $classStubFactory);

        $this->assertTrue($generator->apply($moduleContext));
    }

    public function testGetTestStub(): void
    {
        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContext->method('getTestNamespace')->willReturn('Vendor\\Module\\Test');

        $expectedClassStub = $this->createMock(ClassStub::class);

        $phpGeneratorFactory = new PhpGeneratorFactory(
            $this->createMock(DirectoryList::class),
            $this->createMock(Filesystem::class),
        );

        $classStubFactory = $this->createMock(ClassStubFactory::class);
        $classStubFactory->method('create')->willReturn($expectedClassStub);

        $generator = new ModuleTestGenerator($phpGeneratorFactory, $classStubFactory);

        $result = $generator->getTestStub($moduleContext);
        $this->assertSame($expectedClassStub, $result);
    }

    public function testGenerate(): void
    {
        $moduleName = 'Yireo_TestGenerator';
        $testNamespace = 'Yireo\\TestGenerator\\Test';

        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContext->method('getModuleName')->willReturn($moduleName);
        $moduleContext->method('getTestNamespace')->willReturn($testNamespace);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($this->createMock(WriteInterface::class));

        $phpGenerator = $this->createMock(PhpGeneratorInterface::class);
        $phpGenerator->method('output')->willReturn('foobar');
        $phpGeneratorFactory = $this->createMock(PhpGeneratorFactory::class);
        $phpGeneratorFactory->method('create')->willReturn($phpGenerator);

        $classStub = $this->createMock(ClassStub::class);
        $classStub->method('getClassName')->willReturn('Foobar');

        $classStubFactory = $this->createMock(ClassStubFactory::class);
        $classStubFactory->method('create')->willReturn($classStub);

        $generator = new ModuleTestGenerator($phpGeneratorFactory, $classStubFactory);

        $actualPhpGenerator = $generator->generate($moduleContext);

        $this->assertSame('foobar', $actualPhpGenerator->output());
    }
}
