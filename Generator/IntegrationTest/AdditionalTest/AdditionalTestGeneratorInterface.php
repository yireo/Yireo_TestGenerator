<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest;

use Yireo\TestGenerator\Generator\ModuleContext;
use Yireo\TestGenerator\Generator\PhpGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;

interface AdditionalTestGeneratorInterface
{
    public function apply(ModuleContext $moduleContext): bool;

    public function getTestStub(ModuleContext $moduleContext): ClassStub;

    public function generate(ModuleContext $moduleContext): PhpGeneratorInterface;
}
