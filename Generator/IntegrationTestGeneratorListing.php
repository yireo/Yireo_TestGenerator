<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\ObjectManagerInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\GenericTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\TestGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;

class IntegrationTestGeneratorListing
{
    public function __construct(
        private ObjectManagerInterface $objectManager,
        private GenericTestGenerator $genericTestGenerator,
        private array $testGenerators = [],
    ) {
    }

    public function selectGenerator(ClassStub $classStub): TestGeneratorInterface
    {
        foreach ($this->getGenerators() as $testGenerator) {
            if (false === $testGenerator instanceof TestGeneratorInterface) {
                continue;
            }

            if ($testGenerator->apply($classStub)) {
                return $testGenerator;
            }
        }

        return $this->genericTestGenerator;
    }

    /**
     * @return TestGeneratorInterface[]
     */
    public function getGenerators(): array
    {
        $generators = $this->testGenerators;
        usort($generators, function (array $a, array $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        $result = [];
        foreach ($generators as $generator) {
            $result[] = $this->objectManager->get($generator);
        }

        return $result;
    }
}
