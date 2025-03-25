<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator;

use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\ModuleContextFactory;

// @test-generator-skip-override
class ModuleContextFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $moduleName = 'Yireo_TestGenerator';
        $testType = 'Unit';

        $moduleContext = $this->createMock(ModuleContext::class);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->with(
                ModuleContext::class,
                [
                    'moduleName' => $moduleName,
                    'testType' => $testType,
                ]
            )
            ->willReturn($moduleContext);

        $factory = new ModuleContextFactory($objectManager);
        $result = $factory->create($moduleName, $testType);

        $this->assertSame($moduleContext, $result);
    }

    public function testCreateWithDifferentModuleNameAndTestType(): void
    {
        $moduleName = 'Magento_Catalog';
        $testType = 'Integration';

        $moduleContext = $this->createMock(ModuleContext::class);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->with(
                ModuleContext::class,
                [
                    'moduleName' => $moduleName,
                    'testType' => $testType,
                ]
            )
            ->willReturn($moduleContext);

        $factory = new ModuleContextFactory($objectManager);
        $result = $factory->create($moduleName, $testType);

        $this->assertSame($moduleContext, $result);
    }
}
