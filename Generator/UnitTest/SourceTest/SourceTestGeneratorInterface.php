<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest;

use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

interface SourceTestGeneratorInterface
{
    public function apply(ClassStub $classStub): bool;

    public function generate(ClassStub $classStub, ClassStub $testClassStub): string|PhpGenerator;
}
