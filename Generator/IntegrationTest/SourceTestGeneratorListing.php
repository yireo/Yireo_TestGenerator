<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Magento\Framework\ObjectManagerInterface;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\GenericTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\SourceTestGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;

class SourceTestGeneratorListing
{
    public function __construct(
        private ObjectManagerInterface $objectManager,
        private GenericTestGenerator $genericTestGenerator,
        private array $testGenerators = [],
    ) {
    }

    public function selectGenerator(ClassStub $classStub): SourceTestGeneratorInterface
    {
        foreach ($this->getGenerators() as $testGenerator) {
            if (false === $testGenerator instanceof SourceTestGeneratorInterface) {
                continue;
            }

            if ($testGenerator->apply($classStub)) {
                return $testGenerator;
            }
        }

        return $this->genericTestGenerator;
    }

    /**
     * @return SourceTestGeneratorInterface[]
     */
    public function getGenerators(): array
    {
        $generators = $this->testGenerators;
        if (empty($generators)) {
            return [];
        }

        usort($generators, function (array $a, array $b) {
            return $a['sort_order'] <=> $b['sort_order'];
        });

        $result = [];
        foreach ($generators as $generator) {
            $result[] = $this->objectManager->get($generator['class_name']);
        }

        return $result;
    }
}
