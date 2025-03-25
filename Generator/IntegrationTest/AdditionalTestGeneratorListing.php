<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest\AdditionalTestGeneratorInterface;

class AdditionalTestGeneratorListing
{
    public function __construct(
        private array $testGenerators = [],
    ) {
    }

    /**
     * @return AdditionalTestGeneratorInterface[]
     */
    public function getGenerators(): array
    {
        return $this->testGenerators;
    }
}
