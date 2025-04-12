<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\AdditionalTest;

use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\ModuleContext;

interface AdditionalTestGeneratorInterface
{
    public function apply(ModuleContext $moduleContext, bool $overrideExisting): bool;

    public function generate(ModuleContext $moduleContext, OutputInterface $output): bool;
}
