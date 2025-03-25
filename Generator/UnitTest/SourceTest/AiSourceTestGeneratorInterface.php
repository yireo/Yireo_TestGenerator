<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest;

use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

interface AiSourceTestGeneratorInterface extends SourceTestGeneratorInterface
{
    public function getPrompt(string $className, string $testClassName, string $classContents): string;
}
