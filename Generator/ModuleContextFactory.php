<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Magento\Framework\ObjectManagerInterface;

class ModuleContextFactory
{
    public function __construct(
        private ObjectManagerInterface $objectManager
    ) {
    }

    public function create(
        string $moduleName,
        string $testType,
    ): ModuleContext {
        return $this->objectManager->create(ModuleContext::class, [
            'moduleName' => $moduleName,
            'testType' => $testType,
        ]);
    }
}
