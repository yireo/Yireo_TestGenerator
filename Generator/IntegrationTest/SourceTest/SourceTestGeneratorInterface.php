<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest\SourceTest;

use Yireo\TestGenerator\Generator\PhpGeneratorInterface;
use Yireo\TestGenerator\Model\ClassStub;

interface SourceTestGeneratorInterface
{
    public function apply(ClassStub $classStub): bool;

    public function generate(ClassStub $classStub, ClassStub $testClassStub): PhpGeneratorInterface;
}
