<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
        );

        $this->assertEquals('yireo:test:generate', $command->getName());
        $this->assertEquals('Generate tests for a given module', $command->getDescription());
    }

    public function testExecuteWithEmptyModuleName(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);
        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
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
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
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
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
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
            ->with('Vendor_Module', self::class, $this->isInstanceOf(OutputInterface::class), false);

        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '\\'.self::class]
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
            ->method('generateSourceTests')
            ->with('Vendor_Module', $this->isInstanceOf(OutputInterface::class), false);
        $integrationTestGenerator->expects($this->once())
            ->method('generateAdditionalTests')
            ->with('Vendor_Module', $this->isInstanceOf(OutputInterface::class), false);

        $unitTestGenerator = $this->createMock(UnitTestGenerator::class);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
        );

        /** @var InputInterface|MockObject $input */
        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'integration'],
            ['override-existing', false],
            ['generate-source-tests', '1'],
            ['generate-additional-tests', '1'],
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
            ->with('Vendor_Module', self::class, $this->isInstanceOf(OutputInterface::class), true, false);
        $unitTestGenerator->expects($this->never())->method('generateAll');

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '\\'.self::class]
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
            ->with('Vendor_Module', $this->isInstanceOf(OutputInterface::class), true, true);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);
        $appState = $this->createMock(AppState::class);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $unitTestGenerator,
            $componentRegistrar,
            $appState
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'unit'],
            ['override-existing', true],
            ['ai', true],
        ]);

        $output = $this->createMock(OutputInterface::class);

        $result = $command->run($input, $output);
        $this->assertEquals(0, $result);
    }

    public function testExecuteWithInvalidBooleanOption(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $integrationTestGenerator->expects($this->never())->method('generateSourceTests');

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $this->createMock(UnitTestGenerator::class),
            $componentRegistrar,
            $this->createMock(AppState::class)
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'integration'],
            ['override-existing', 'maybe'],
        ]);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with('<error>Invalid boolean value for an option</error>');

        $this->assertEquals(2, $command->run($input, $output));
    }

    public function testExecuteParsesBooleanOptions(): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $integrationTestGenerator->expects($this->never())->method('generateSourceTests');
        $integrationTestGenerator->expects($this->once())
            ->method('generateAdditionalTests')
            ->with('Vendor_Module', $this->isInstanceOf(OutputInterface::class), true);

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $this->createMock(UnitTestGenerator::class),
            $componentRegistrar,
            $this->createMock(AppState::class)
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '']
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'integration'],
            ['override-existing', null],
            ['generate-source-tests', 'false'],
            ['generate-additional-tests', 'yes'],
        ]);

        $this->assertEquals(0, $command->run($input, $this->createMock(OutputInterface::class)));
    }

    #[DataProvider('getInvalidClassNames')]
    public function testExecuteWithInvalidClassName(string $className): void
    {
        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $integrationTestGenerator->expects($this->never())->method('generateTestPerClass');

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $this->createMock(UnitTestGenerator::class),
            $componentRegistrar,
            $this->createMock(AppState::class)
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', $className]
        ]);
        $input->method('getOption')->willReturnMap([
            ['type', 'integration'],
            ['override-existing', false],
        ]);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with('<error>Class name is not a valid class of module Vendor_Module</error>');

        $this->assertEquals(2, $command->run($input, $output));
    }

    public static function getInvalidClassNames(): array
    {
        return [
            'no namespace' => ['SomeClass'],
            'path traversal' => ['Vendor\\..\\..\\etc\\passwd'],
            'slashes' => ['Vendor/Module/Foo'],
            'trailing separator' => ['Vendor\\Module\\'],
            'unknown class' => ['Vendor\\Module\\DoesNotExist'],
            'class outside module' => [TestCase::class],
        ];
    }

    public function testExecuteInProductionModeWithoutForce(): void
    {
        $appState = $this->createMock(AppState::class);
        $appState->method('getMode')->willReturn(AppState::MODE_PRODUCTION);
        $appState->expects($this->never())->method('setAreaCode');

        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $integrationTestGenerator->expects($this->never())->method('generateSourceTests');

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $this->createMock(UnitTestGenerator::class),
            $this->createMock(ComponentRegistrar::class),
            $appState
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getOption')->willReturnMap([['force', false]]);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with('<error>Generating tests is not allowed in production mode, use --force to override</error>');

        $this->assertEquals(2, $command->run($input, $output));
    }

    public function testExecuteInProductionModeWithForceAndAreaAlreadySet(): void
    {
        $appState = $this->createMock(AppState::class);
        $appState->method('getMode')->willReturn(AppState::MODE_PRODUCTION);
        $appState->method('getAreaCode')->willReturn('adminhtml');
        $appState->expects($this->never())->method('setAreaCode');

        $integrationTestGenerator = $this->createMock(IntegrationTestGenerator::class);
        $integrationTestGenerator->expects($this->once())->method('generateSourceTests');

        $componentRegistrar = $this->createMock(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn(__DIR__);

        $command = new GenerateCommand(
            $integrationTestGenerator,
            $this->createMock(UnitTestGenerator::class),
            $componentRegistrar,
            $appState
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['moduleName', 'Vendor_Module'],
            ['className', '']
        ]);
        $input->method('getOption')->willReturnMap([
            ['force', true],
            ['type', 'integration'],
            ['override-existing', false],
            ['generate-source-tests', '1'],
            ['generate-additional-tests', '0'],
        ]);

        $this->assertEquals(0, $command->run($input, $this->createMock(OutputInterface::class)));
    }

    public function testExecuteSetsAreaCodeWhenMissing(): void
    {
        $appState = $this->createMock(AppState::class);
        $appState->method('getAreaCode')->willThrowException(
            new \Magento\Framework\Exception\LocalizedException(__('Area code is not set'))
        );
        $appState->expects($this->once())->method('setAreaCode')->with('frontend');

        $command = new GenerateCommand(
            $this->createMock(IntegrationTestGenerator::class),
            $this->createMock(UnitTestGenerator::class),
            $this->createMock(ComponentRegistrar::class),
            $appState
        );

        $input = $this->createMock(InputInterface::class);
        $input->method('getArgument')->with('moduleName')->willReturn('');

        $this->assertEquals(2, $command->run($input, $this->createMock(OutputInterface::class)));
    }
}
