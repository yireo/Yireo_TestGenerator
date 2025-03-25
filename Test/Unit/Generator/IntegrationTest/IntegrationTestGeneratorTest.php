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
    public function testGenerateAll(): void
    {
        // Mock dependencies
        $classCollector = $this->createMock(ClassCollector::class);
        $classStubFactory = $this->createMock(ClassStubFactory::class);
        $moduleContextFactory = $this->createMock(ModuleContextFactory::class);
        $sourceTestGeneratorListing = $this->createMock(SourceTestGeneratorListing::class);
        $additionalTestGeneratorListing = $this->createMock(AdditionalTestGeneratorListing::class);

        $generator = new IntegrationTestGenerator(
            $classCollector,
            $classStubFactory,
            $moduleContextFactory,
            $sourceTestGeneratorListing,
            $additionalTestGeneratorListing
        );

        $output = $this->createMock(OutputInterface::class);
        $generator->generateAll('Vendor_Module', $output, true);
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
}
