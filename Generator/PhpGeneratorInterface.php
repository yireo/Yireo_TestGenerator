<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;

interface PhpGeneratorInterface
{
    public function getClassType(): ClassType;
    public function addClassMethod(string $methodName, string $methodBody): void;
    public function addTrait(string $traitName): void;
    public function addUse(string $namespace, ?string $alias = null): void;
    public function addConstant(string $name, string $value): void;
    public function output(): string;
}
