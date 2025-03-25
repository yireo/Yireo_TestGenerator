<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\IntegrationTest;

use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\GenericTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\SourceTestGeneratorInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTestGeneratorListing;
use Yireo\TestGenerator\Model\ClassStub;

// @test-generator-skip-override
class SourceTestGeneratorListingTest extends TestCase
{
    public function testSelectGenerator(): void
    {
        $classStub = $this->createMock(ClassStub::class);

        // Case 1: No generators apply
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);
        $testGenerators = [];

        $sourceTestGeneratorListing = new SourceTestGeneratorListing(
            $objectManager,
            $genericTestGenerator,
            $testGenerators
        );

        $result = $sourceTestGeneratorListing->selectGenerator($classStub);
        $this->assertSame($genericTestGenerator, $result);

        // Case 2: One generator applies
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);

        $testGenerator = $this->createMock(SourceTestGeneratorInterface::class);
        $testGenerator->expects($this->once())
            ->method('apply')
            ->with($classStub)
            ->willReturn(true);

        $objectManager->expects($this->once())
            ->method('get')
            ->willReturn($testGenerator);

        $testGenerators = [
            ['class_name' => 'TestGenerator', 'sort_order' => 10]
        ];

        $sourceTestGeneratorListing = new SourceTestGeneratorListing(
            $objectManager,
            $genericTestGenerator,
            $testGenerators
        );

        $result = $sourceTestGeneratorListing->selectGenerator($classStub);
        $this->assertSame($testGenerator, $result);

        // Case 3: Multiple generators, but only one applies
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);

        $testGenerator1 = $this->createMock(SourceTestGeneratorInterface::class);
        $testGenerator1->expects($this->once())
            ->method('apply')
            ->with($classStub)
            ->willReturn(false);

        $testGenerator2 = $this->createMock(SourceTestGeneratorInterface::class);
        $testGenerator2->expects($this->once())
            ->method('apply')
            ->with($classStub)
            ->willReturn(true);

        $objectManager->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls($testGenerator1, $testGenerator2);

        $testGenerators = [
            ['class_name' => 'TestGenerator1', 'sort_order' => 10],
            ['class_name' => 'TestGenerator2', 'sort_order' => 20],
        ];

        $sourceTestGeneratorListing = new SourceTestGeneratorListing(
            $objectManager,
            $genericTestGenerator,
            $testGenerators
        );

        $result = $sourceTestGeneratorListing->selectGenerator($classStub);
        $this->assertSame($testGenerator2, $result);

        // Case 4: Invalid generator type
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);

        $testGenerator = $this->createMock(\stdClass::class);

        $objectManager->expects($this->once())
            ->method('get')
            ->willReturn($testGenerator);

        $testGenerators = [
            ['class_name' => 'TestGenerator', 'sort_order' => 10]
        ];

        $sourceTestGeneratorListing = new SourceTestGeneratorListing(
            $objectManager,
            $genericTestGenerator,
            $testGenerators
        );

        $result = $sourceTestGeneratorListing->selectGenerator($classStub);
        $this->assertSame($genericTestGenerator, $result);
    }

    public function testGetGenerators(): void
    {
        // Case 1: Empty generators array
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);
        $testGenerators = [];

        $sourceTestGeneratorListing = new SourceTestGeneratorListing(
            $objectManager,
            $genericTestGenerator,
            $testGenerators
        );

        $result = $sourceTestGeneratorListing->getGenerators();
        $this->assertIsArray($result);
        $this->assertEmpty($result);

        // Case 2: Multiple generators with different sort orders
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);

        $testGenerator1 = $this->createMock(SourceTestGeneratorInterface::class);
        $testGenerator2 = $this->createMock(SourceTestGeneratorInterface::class);
        $testGenerator3 = $this->createMock(SourceTestGeneratorInterface::class);

        $objectManager->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                ['TestGenerator3', $testGenerator3],
                ['TestGenerator1', $testGenerator1],
                ['TestGenerator2', $testGenerator2],
            ]);

        $testGenerators = [
            ['class_name' => 'TestGenerator3', 'sort_order' => 30],
            ['class_name' => 'TestGenerator1', 'sort_order' => 10],
            ['class_name' => 'TestGenerator2', 'sort_order' => 20],
        ];

        $sourceTestGeneratorListing = new SourceTestGeneratorListing(
            $objectManager,
            $genericTestGenerator,
            $testGenerators
        );

        $result = $sourceTestGeneratorListing->getGenerators();
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame($testGenerator1, $result[0]);
        $this->assertSame($testGenerator2, $result[1]);
        $this->assertSame($testGenerator3, $result[2]);

        // Case 3: Multiple generators with same sort order
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $genericTestGenerator = $this->createMock(GenericTestGenerator::class);

        $testGenerator1 = $this->createMock(SourceTestGeneratorInterface::class);
        $testGenerator2 = $this->createMock(SourceTestGeneratorInterface::class);

        $objectManager->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['TestGenerator1', $testGenerator1],
                ['TestGenerator2', $testGenerator2],
            ]);

        $testGenerators = [
            ['class_name' => 'TestGenerator1', 'sort_order' => 10],
            ['class_name' => 'TestGenerator2', 'sort_order' => 10],
        ];

        $sourceTestGeneratorListing = new SourceTestGeneratorListing(
            $objectManager,
            $genericTestGenerator,
            $testGenerators
        );

        $result = $sourceTestGeneratorListing->getGenerators();
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }
}
