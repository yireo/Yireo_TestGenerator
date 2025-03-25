<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\IntegrationTest;

use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest\AdditionalTestGeneratorInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTestGeneratorListing;

// @test-generator-skip-override
class AdditionalTestGeneratorListingTest extends TestCase
{
    /**
     * Test getGenerators method with empty array
     */
    public function testGetGeneratorsWithEmptyArray(): void
    {
        $testGenerators = [];
        $listing = new AdditionalTestGeneratorListing($testGenerators);
        $this->assertSame($testGenerators, $listing->getGenerators());
    }

    /**
     * Test getGenerators method with populated array
     */
    public function testGetGeneratorsWithPopulatedArray(): void
    {
        $mockGenerator = $this->createMock(AdditionalTestGeneratorInterface::class);
        $testGenerators = [$mockGenerator];
        $listing = new AdditionalTestGeneratorListing($testGenerators);
        $this->assertSame($testGenerators, $listing->getGenerators());
    }

    /**
     * Test getGenerators method with multiple generators
     */
    public function testGetGeneratorsWithMultipleGenerators(): void
    {
        $mockGenerator1 = $this->createMock(AdditionalTestGeneratorInterface::class);
        $mockGenerator2 = $this->createMock(AdditionalTestGeneratorInterface::class);
        $testGenerators = [$mockGenerator1, $mockGenerator2];
        $listing = new AdditionalTestGeneratorListing($testGenerators);
        $this->assertSame($testGenerators, $listing->getGenerators());
    }
}
