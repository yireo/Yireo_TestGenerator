<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Utilities;

use PhpToken;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class ClassCollector
{
    public function collect(string $folder): array
    {
        $classes = [];
        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $file = $file[0];
            if (strstr($file, '/Test/')) {
                continue;
            }

            $class = $this->getClassNameFromFile((string)$file);
            if (empty($class)) {
                continue;
            }

            $classes[$file] = $class;
        }

        return $classes;
    }

    private function getClassNameFromFile(string $file): string
    {
        $className = '';
        $namespace = '';
        $tokens    = PhpToken::tokenize(file_get_contents($file));

        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i]->getTokenName() === 'T_NAMESPACE') {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j]->getTokenName() === 'T_NAME_QUALIFIED') {
                        $namespace = $tokens[$j]->text;
                        break;
                    }
                }
            }

            if ($tokens[$i]->getTokenName() === 'T_CLASS') {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j]->getTokenName() === 'T_WHITESPACE') {
                        continue;
                    }

                    if ($tokens[$j]->getTokenName() === 'T_STRING') {
                        $className = $namespace . '\\' . $tokens[$j]->text;
                    } else {
                        break;
                    }
                }
            }
        }

        return $className;
    }
}
