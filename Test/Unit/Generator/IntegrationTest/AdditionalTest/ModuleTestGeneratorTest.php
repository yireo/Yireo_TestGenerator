<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\IntegrationTest\AdditionalTest;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest\ModuleTestGenerator;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Generator\PhpGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Model\ClassStubFactory;

// @test-generator-skip-override
class ModuleTestGeneratorTest extends TestCase
{
    private string $tmpModulePath = '';

    protected function tearDown(): void
    {
        if ($this->tmpModulePath !== '') {
            @unlink($this->tmpModulePath.'/etc/module.xml');
            @rmdir($this->tmpModulePath.'/etc');
            @rmdir($this->tmpModulePath);
        }
    }

    public function testApplyWithOverrideExisting(): void
    {
        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContext->expects($this->never())->method('getWriter');

        $this->assertTrue($this->getGenerator()->apply($moduleContext, true));
    }

    public function testApplyWithNonExistingTest(): void
    {
        $writer = $this->createMock(WriteInterface::class);
        $writer->method('isExist')->with('/tmp/module/Test/Integration/ModuleTest.php')->willReturn(false);

        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContext->method('getWriter')->willReturn($writer);
        $moduleContext->method('getTestPath')->willReturn('/tmp/module/Test/Integration/');

        $this->assertTrue($this->getGenerator()->apply($moduleContext, false));
    }

    public function testApplyWithExistingTest(): void
    {
        $writer = $this->createMock(WriteInterface::class);
        $writer->method('isExist')->willReturn(true);

        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContext->method('getWriter')->willReturn($writer);
        $moduleContext->method('getTestPath')->willReturn('/tmp/module/Test/Integration/');

        $this->assertFalse($this->getGenerator()->apply($moduleContext, false));
    }

    public function testGenerate(): void
    {
        $moduleName = 'Vendor_Module';
        $this->tmpModulePath = sys_get_temp_dir().'/'.uniqid('module-test-generator-');
        mkdir($this->tmpModulePath.'/etc', 0777, true);
        file_put_contents(
            $this->tmpModulePath.'/etc/module.xml',
            '<?xml version="1.0"?><config><module name="Vendor_Module">'
            .'<sequence><module name="Magento_Catalog"/></sequence></module></config>'
        );

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')
            ->with(ComponentRegistrar::MODULE, $moduleName)
            ->willReturn($this->tmpModulePath);

        $writer = $this->createMock(WriteInterface::class);
        $writer->expects($this->once())
            ->method('writeFile')
            ->with('/tmp/module/Test/Integration/ModuleTest.php', 'foobar');

        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContext->method('getModuleName')->willReturn($moduleName);
        $moduleContext->method('getTestNamespace')->willReturn('Vendor\\Module\\Test\\Integration');
        $moduleContext->method('getTestPath')->willReturn('/tmp/module/Test/Integration/');
        $moduleContext->method('getWriter')->willReturn($writer);

        $phpGenerator = $this->createMock(PhpGeneratorInterface::class);
        $phpGenerator->method('output')->willReturn('foobar');
        $phpGenerator->expects($this->once())
            ->method('addClassMethod')
            ->with('testModule', $this->callback(function (string $body): bool {
                return str_contains($body, "'Vendor_Module'") && str_contains($body, "'Magento_Catalog'");
            }));

        $phpGeneratorFactory = $this->createMock(PhpGeneratorFactory::class);
        $phpGeneratorFactory->method('create')->willReturn($phpGenerator);

        $classStubFactory = $this->createMock(ClassStubFactory::class);
        $classStubFactory->method('create')
            ->with($moduleName, 'Vendor\\Module\\Test\\Integration\\ModuleTest')
            ->willReturn($this->createMock(ClassStub::class));

        $generator = new ModuleTestGenerator($componentRegistrar, $phpGeneratorFactory, $classStubFactory);

        $this->assertTrue($generator->generate($moduleContext, $this->createMock(OutputInterface::class)));
    }

    private function getGenerator(): ModuleTestGenerator
    {
        return new ModuleTestGenerator(
            $this->createMock(ComponentRegistrar::class),
            $this->createMock(PhpGeneratorFactory::class),
            $this->createMock(ClassStubFactory::class)
        );
    }
}
