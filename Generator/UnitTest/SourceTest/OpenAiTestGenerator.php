<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest\SourceTest;

use Yireo\TestGenerator\Config\Config;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Utilities\OpenAiClientWrapper;

class OpenAiTestGenerator implements AiSourceTestGeneratorInterface
{
    public function __construct(
        private Config $config,
        private OpenAiClientWrapper $clientWrapper,
    ) {
    }

    public function apply(ClassStub $classStub): bool
    {
        if (false === $this->config->isOpenAiEnabled()) {
            return false;
        }

        $openAiApiKey = $this->config->getOpenAiApiKey();

        return !empty($openAiApiKey);
    }

    public function generate(ClassStub $classStub, ClassStub $testClassStub): string|PhpGenerator
    {
        $openAiApiKey = $this->config->getOpenAiApiKey();
        $classContents = $classStub->getContents();

        $prompt = $this->getPrompt(
            $classStub->getFullQualifiedClassName(),
            $testClassStub->getFullQualifiedClassName(),
            $classContents,
        );

        $contents = $this->clientWrapper->chat($prompt, $openAiApiKey);

        $contents = str_replace('```php', '', $contents);
        $contents = str_replace('```', '', $contents);

        return trim($contents);
    }

    public function getPrompt(string $className, string $testClassName, string $classContents): string
    {
        return <<<EOF
Given the following PHP class `{$className}` with the following contents:

```php
{$classContents}
```

Generate a new test class `{$testClassName}` implementing PHPUnit 9. Make sure to add a test method per original method. Add PHP 8 type hints (return types, argument types). Add a `declare(strict_types=1)` statement in the top. When an original method contains arguments, test out various variants of these arguments.

Do not use `setUp` method in the test class. Instead, create all test dependencies in each test method.

Do not test private or protected methods.

Do not give a confirmation text. Do not add an explanation. Just return the PHP text.
EOF;
    }
}
