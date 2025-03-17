<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\IntegrationTest;

use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

interface TestGeneratorInterface
{
    public function apply(ClassStub $classStub): bool;

    public function generate(ClassStub $classStub, ClassStub $testClassStub): PhpGenerator;
}
