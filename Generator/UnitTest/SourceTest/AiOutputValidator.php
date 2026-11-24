<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest;

use ParseError;
use RuntimeException;

class AiOutputValidator
{
    private const TRIM_CHARACTERS = " \f\n\r\t\v\x00";

    public function validate(string $response, string $expectedClassName): string
    {
        $code = $this->extractCode($response);
        if (false === str_starts_with($code, '<?php')) {
            throw new RuntimeException('AI response does not contain PHP code');
        }

        try {
            token_get_all($code, TOKEN_PARSE);
        } catch (ParseError $parseError) {
            throw new RuntimeException('AI response contains invalid PHP: '.$parseError->getMessage(), 0, $parseError);
        }

        $expectedClassName = trim($expectedClassName, '\\');
        $separator = strrpos($expectedClassName, '\\');
        if (false === $separator) {
            throw new RuntimeException('Expected test class "'.$expectedClassName.'" has no namespace');
        }

        $namespace = substr($expectedClassName, 0, $separator);
        if (!preg_match('/^namespace\s+'.preg_quote($namespace, '/').'\s*;/m', $code)) {
            throw new RuntimeException('AI response does not use namespace "'.$namespace.'"');
        }

        $className = substr($expectedClassName, $separator + 1);
        $classPattern = '/^\s*(?:(?:final|abstract|readonly)\s+)*class\s+'.preg_quote($className, '/').'\b/m';
        if (!preg_match($classPattern, $code)) {
            throw new RuntimeException('AI response does not define class "'.$className.'"');
        }

        return $code;
    }

    private function extractCode(string $response): string
    {
        if (preg_match('/```(?:php)?[ \t]*\r?\n(.*?)```/s', $response, $matches)) {
            return trim($matches[1], self::TRIM_CHARACTERS);
        }

        return trim($response, self::TRIM_CHARACTERS);
    }
}
