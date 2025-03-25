<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yireo\TestGenerator\Generator\ModuleContext;

// @test-generator-skip-override
class ModuleContextTest extends TestCase
{
    public function testGetModuleName(): void
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);
        $moduleName = 'Vendor_Module';

        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName
        );

        $this->assertEquals($moduleName, $moduleContext->getModuleName());
    }

    public function testGetPath(): void
    {
        $moduleName = 'Vendor_Module';
        $modulePath = '/path/to/module';

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, $moduleName)
            ->willReturn($modulePath);

        $filesystem = $this->createMock(Filesystem::class);

        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName
        );

        $this->assertEquals($modulePath, $moduleContext->getPath());
    }

    public function testGetPathThrowsExceptionWhenNoPathFound(): void
    {
        $moduleName = 'Vendor_Module';

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, $moduleName)
            ->willReturn('');

        $filesystem = $this->createMock(Filesystem::class);

        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No path found for module "Vendor_Module"');

        $moduleContext->getPath();
    }

    public function testGetTestPath(): void
    {
        $moduleName = 'Vendor_Module';
        $modulePath = '/path/to/module';
        $testPath = $modulePath . '/Test/Integration/';

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, $moduleName)
            ->willReturn($modulePath);

        /** @var WriteInterface|MockObject $writer */
        $writer = $this->createMock(WriteInterface::class);
        $writer->expects($this->once())
            ->method('isExist')
            ->with($testPath)
            ->willReturn(true);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($writer);

        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName
        );

        $this->assertEquals($testPath, $moduleContext->getTestPath());
    }

    public function testGetTestPathCreatesDirectoryWhenNotExists(): void
    {
        $moduleName = 'Vendor_Module';
        $modulePath = '/path/to/module';
        $testPath = $modulePath . '/Test/Integration/';

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn($modulePath);

        /** @var WriteInterface|MockObject $writer */
        $writer = $this->createMock(WriteInterface::class);
        $writer->method('isExist')->with($testPath)->willReturn(false);
        $writer->method('create')->with($testPath);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($writer);

        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName
        );

        $this->assertEquals($testPath, $moduleContext->getTestPath());
    }

    public function testGetTestNamespace(): void
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $filesystem = $this->createMock(Filesystem::class);
        $moduleName = 'Vendor_Module';

        // Default test type is 'integration'
        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName
        );

        $this->assertEquals('Vendor\\Module\\Test\\Integration', $moduleContext->getTestNamespace());

        // Different test type
        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName,
            'unit'
        );

        $this->assertEquals('Vendor\\Module\\Test\\Unit', $moduleContext->getTestNamespace());
    }

    public function testGetWriter(): void
    {
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);

        $writer = $this->createMock(WriteInterface::class);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($writer);

        $moduleName = 'Vendor_Module';

        $moduleContext = new ModuleContext(
            $componentRegistrar,
            $filesystem,
            $moduleName
        );

        $this->assertSame($writer, $moduleContext->getWriter());
    }
}
