<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Yireo\TestGenerator\Console\Command\GenerateCommand;
use Yireo\TestGenerator\Generator\IntegrationTest\IntegrationTestGenerator;
use Yireo\TestGenerator\Generator\UnitTest\UnitTestGenerator;

// @test-generator-skip-override
class GenerateCommandTest extends TestCase
{
    public function testConfigure(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        $this->assertEquals('yireo:test:generate', $command->getName());
        $this->assertEquals('Generate tests for a given module', $command->getDescription());
    }

    public function testExecuteWithEmptyModuleName(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('moduleName')->willReturn('');

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with('<error>No module name given as argument</error>');

        $result = $command->run($input, $output);
        $this->assertEquals(2, $result);
    }

    public function testExecuteWithInvalidModuleName(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn('/invalid/path');

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('moduleName')->willReturn('Vendor_Module');

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with('<error>Module name is not registered</error>');

        $result = $command->run($input, $output);
        $this->assertEquals(2, $result);
    }

    public function testExecuteWithInvalidType(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        /** @var InputInterface|MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('moduleName')->willReturn('Vendor_Module');
        $input->method('getOption')->willReturnMap([
            ['type', 'invalid'],
            ['override-existing', false]
        ]);

        /** @var OutputInterface|MockObject $output */
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with('<error>Unsupported type</error>');

        $result = $command->run($input, $output);
        $this->assertEquals(2, $result);
    }

    public function testExecuteIntegrationTestWithClassName(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $integrationTestGenerator->expects($this->once())
            ->method('generateTestPerClass')
            ->with('Vendor_Module', 'SomeClass', $this->isInstanceOf(OutputInterface::class), false);

        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', 'SomeClass']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'integration'],
            ['override-existing', false]
        ]);

        $output = $this->createMock(OutputInterface::class);

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
    }

    public function testExecuteIntegrationTestWithoutClassName(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $integrationTestGenerator->expects($this->once())
            ->method('generateAll')
            ->with('Vendor_Module', $this->isInstanceOf(OutputInterface::class), false);

        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        /** @var InputInterface|MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'integration'],
            ['override-existing', false]
        ]);

        /** @var OutputInterface|MockObject $output */
        $output = $this->createMock(OutputInterface::class);

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
    }

    public function testExecuteUnitTestWithClassName(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);

        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);
        $unitTestGenerator->expects($this->once())
            ->method('generateTestPerClass')
            ->with('Vendor_Module', 'SomeClass', $this->isInstanceOf(OutputInterface::class), true);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', 'SomeClass']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'unit'],
            ['override-existing', true]
        ]);

        $output = $this->createMock(OutputInterface::class);

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
    }

    public function testExecuteUnitTestWithoutClassName(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);

        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);
        $unitTestGenerator->expects($this->once())
            ->method('generateAll')
            ->with('Vendor_Module', $this->isInstanceOf(OutputInterface::class), true);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'unit'],
            ['override-existing', true]
        ]);

        $output = $this->createMock(OutputInterface::class);

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
    }
}
