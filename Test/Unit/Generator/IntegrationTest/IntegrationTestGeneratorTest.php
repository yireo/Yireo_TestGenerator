<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\IntegrationTest;

use Magento\Framework\Filesystem\File\WriteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest\AdditionalTestGeneratorInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTestGeneratorListing;
use Yireo\TestGenerator\Generator\IntegrationTest\IntegrationTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTestGeneratorListing;
use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\ModuleContextFactory;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Model\ClassStubFactory;
use Yireo\TestGenerator\Utilities\ClassCollector;

// @test-generator-skip-override
class IntegrationTestGeneratorTest extends TestCase
{
    public function testGenerateAdditionalTests(): void
    {
        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContextFactory = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactory->method('create')
            ->with('Vendor_Module', 'integration')
            ->willReturn($moduleContext);

        $output = $this->createMock(OutputInterface::class);

        $appliedTestGenerator = $this->createMock(AdditionalTestGeneratorInterface::class);
        $appliedTestGenerator->method('apply')->with($moduleContext, true)->willReturn(true);
        $appliedTestGenerator->expects($this->once())->method('generate')->with($moduleContext, $output);

        $skippedTestGenerator = $this->createMock(AdditionalTestGeneratorInterface::class);
        $skippedTestGenerator->method('apply')->willReturn(false);
        $skippedTestGenerator->expects($this->never())->method('generate');

        $additionalTestGeneratorListing = new AdditionalTestGeneratorListing([
            $appliedTestGenerator,
            $skippedTestGenerator,
        ]);

        $generator = new IntegrationTestGenerator(
            $this->createMock(ClassCollector::class),
            $this->createMock(ClassStubFactory::class),
            $moduleContextFactory,
            $this->createMock(SourceTestGeneratorListing::class),
            $additionalTestGeneratorListing
        );

        $generator->generateAdditionalTests('Vendor_Module', $output, true);
    }

    public function testGenerateTestPerClassWithExistingTest(): void
    {
        $classCollector = $this->createMock(ClassCollector::class);
        $classStubFactory = $this->createMock(ClassStubFactory::class);
        $classStubFactory->method('create')->willReturn($this->createMock(ClassStub::class));
        $classStubFactory->method('createTest')->willReturn($this->createMock(ClassStub::class));

        $moduleContextFactory = $this->createMock(ModuleContextFactory::class);
        $sourceTestGeneratorListing = $this->createMock(SourceTestGeneratorListing::class);
        $additionalTestGeneratorListing = $this->createMock(AdditionalTestGeneratorListing::class);

        $output = $this->createMock(OutputInterface::class);

        $generator = new IntegrationTestGenerator(
            $classCollector,
            $classStubFactory,
            $moduleContextFactory,
            $sourceTestGeneratorListing,
            $additionalTestGeneratorListing
        );

        $generator->generateTestPerClass('Vendor_Module', __CLASS__, $output, false);
    }

    public function testGenerateTestPerClassSkipsTestWithSkipMarker(): void
    {
        $testClassStub = $this->createMock(ClassStub::class);
        $testClassStub->method('getRelativePath')->willReturn('Test/Integration/FooTest.php');

        $classStubFactory = $this->createMock(ClassStubFactory::class);
        $classStubFactory->method('create')->willReturn($this->createMock(ClassStub::class));
        $classStubFactory->method('createTest')->willReturn($testClassStub);

        $writer = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $writer->method('isExist')->with('/module/Test/Integration/FooTest.php')->willReturn(true);
        $writer->method('readFile')->willReturn('<?php // @test-generator-skip-override');
        $writer->expects($this->never())->method('writeFile');

        $moduleContext = $this->createMock(ModuleContext::class);
        $moduleContext->method('getModuleName')->willReturn('Vendor_Module');
        $moduleContext->method('getPath')->willReturn('/module');
        $moduleContext->method('getWriter')->willReturn($writer);

        $moduleContextFactory = $this->createMock(ModuleContextFactory::class);
        $moduleContextFactory->method('create')->willReturn($moduleContext);

        $sourceTestGeneratorListing = $this->createMock(SourceTestGeneratorListing::class);
        $sourceTestGeneratorListing->expects($this->never())->method('selectGenerator');

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())->method('writeln')->with('Skipping override for Vendor\\Module\\Foo');

        $generator = new IntegrationTestGenerator(
            $this->createMock(ClassCollector::class),
            $classStubFactory,
            $moduleContextFactory,
            $sourceTestGeneratorListing,
            $this->createMock(AdditionalTestGeneratorListing::class)
        );

        $generator->generateTestPerClass('Vendor_Module', 'Vendor\\Module\\Foo', $output, true);
    }
}
