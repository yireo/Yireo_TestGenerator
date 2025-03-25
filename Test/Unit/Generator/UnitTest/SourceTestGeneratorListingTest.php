<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\UnitTest;

use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\GenericSourceTestGenerator;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\SourceTestGeneratorInterface;
use Yireo\TestGenerator\Generator\UnitTest\SourceTestGeneratorListing;
use Yireo\TestGenerator\Model\ClassStub;

// @test-generator-skip-override
class SourceTestGeneratorListingTest extends TestCase
{
    public function testSelectGeneratorWithMatchingGenerator(): void
    {
        $classStub = $this->createMock(ClassStub::class);

        $matchingGenerator = $this->createMock(SourceTestGeneratorInterface::class);
        $matchingGenerator->expects($this->once())
            ->method('apply')
            ->with($classStub)
            ->willReturn(true);

        $nonMatchingGenerator = $this->createMock(SourceTestGeneratorInterface::class);
        $nonMatchingGenerator->expects($this->never())
            ->method('apply');

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->exactly(1))
            ->method('get')
            ->willReturn($matchingGenerator);

        $genericGenerator = $this->createMock(GenericSourceTestGenerator::class);

        $testGenerators = [
            [
                'class_name' => 'TestGenerator1',
                'sort_order' => 10
            ]
        ];

        $listing = new SourceTestGeneratorListing(
            $objectManager,
            $genericGenerator,
            $testGenerators
        );

        $result = $listing->selectGenerator($classStub);
        $this->assertSame($matchingGenerator, $result);
    }

    public function testSelectGeneratorWithNoMatchingGenerator(): void
    {
        $classStub = $this->createMock(ClassStub::class);

        $nonMatchingGenerator = $this->createMock(SourceTestGeneratorInterface::class);
        $nonMatchingGenerator->expects($this->once())
            ->method('apply')
            ->with($classStub)
            ->willReturn(false);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->exactly(1))
            ->method('get')
            ->willReturn($nonMatchingGenerator);

        $genericGenerator = $this->createMock(GenericSourceTestGenerator::class);

        $testGenerators = [
            [
                'class_name' => 'TestGenerator1',
                'sort_order' => 10
            ]
        ];

        $listing = new SourceTestGeneratorListing(
            $objectManager,
            $genericGenerator,
            $testGenerators
        );

        $result = $listing->selectGenerator($classStub);
        $this->assertSame($genericGenerator, $result);
    }

    public function testSelectGeneratorWithInvalidGenerator(): void
    {
        $classStub = $this->createMock(ClassStub::class);

        $invalidGenerator = $this->createMock(\stdClass::class);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->exactly(1))
            ->method('get')
            ->willReturn($invalidGenerator);

        $genericGenerator = $this->createMock(GenericSourceTestGenerator::class);

        $testGenerators = [
            [
                'class_name' => 'InvalidGenerator',
                'sort_order' => 10
            ]
        ];

        $listing = new SourceTestGeneratorListing(
            $objectManager,
            $genericGenerator,
            $testGenerators
        );

        $result = $listing->selectGenerator($classStub);
        $this->assertSame($genericGenerator, $result);
    }

    public function testGetGenerators(): void
    {
        $generator1 = $this->createMock(SourceTestGeneratorInterface::class);
        $generator2 = $this->createMock(SourceTestGeneratorInterface::class);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['TestGenerator2', $generator2],
                ['TestGenerator1', $generator1]
            ]);

        $genericGenerator = $this->createMock(GenericSourceTestGenerator::class);

        $testGenerators = [
            [
                'class_name' => 'TestGenerator2',
                'sort_order' => 20
            ],
            [
                'class_name' => 'TestGenerator1',
                'sort_order' => 10
            ]
        ];

        $listing = new SourceTestGeneratorListing(
            $objectManager,
            $genericGenerator,
            $testGenerators
        );

        $result = $listing->getGenerators();
        $this->assertCount(2, $result);
        $this->assertSame($generator1, $result[0]);
        $this->assertSame($generator2, $result[1]);
    }

    public function testGetGeneratorsWithEmptyArray(): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->never())
            ->method('get');

        $genericGenerator = $this->createMock(GenericSourceTestGenerator::class);

        $listing = new SourceTestGeneratorListing(
            $objectManager,
            $genericGenerator,
            []
        );

        $result = $listing->getGenerators();
        $this->assertEmpty($result);
    }
}
