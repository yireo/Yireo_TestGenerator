<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Generator\UnitTest;

use Anthropic;
use Yireo\TestGenerator\Config\Config;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Model\ClassStub;

class AnthropicAiTestGenerator implements TestGeneratorInterface
{
    public function __construct(
        private Config $config,
    ) {
    }

    public function apply(ClassStub $classStub): bool
    {
        if (false === $this->config->isAnthropicEnabled()) {
            return false;
        }

        $anthropicAiApiKey = $this->config->getAnthropicApiKey();

        return !empty($anthropicAiApiKey);
    }

    public function generate(ClassStub $classStub, ClassStub $testClassStub): string|PhpGenerator
    {
        $anthropicAiApiKey = $this->config->getAnthropicApiKey();
        $client = Anthropic::client($anthropicAiApiKey);


        $classPath = $classStub->getAbsolutePath();
        $classContents = file_get_contents($classPath); // @todo: Move this to ClassStub

        $prompt = $this->getPrompt(
            $classStub->getFullQualifiedClassName(),
            $testClassStub->getFullQualifiedClassName(),
            $classContents,
        );

        $result = $client->messages()->create([
            'model' => 'claude-3-7-sonnet-20250219', // @todo: Determine this automatically
            'max_tokens' => 8192,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ]);

        $contents = $result->content[0]->text;

        $contents = str_replace('```php', '', $contents);
        $contents = str_replace('```', '', $contents);

        return trim($contents);
    }

    private function getPrompt(string $className, string $testClassName, string $classContents): string
    {
        return <<<EOF
Given the following PHP class `{$className}` with the following contents:

```php
{$classContents}
```

Generate a new test class `{$testClassName}` implementing PHPUnit 9. Make sure to add a test method per original method. Add PHP 8 type hints (return types, argument types). Add a `declare(strict_types=1)` statement in the top. When an original method contains arguments, test out various variants of these arguments.

Do not use `setUp` method in the test class. Instead, create all test dependencies in each test method.

Do not give a confirmation text. Do not add an explanation. Just return the PHP text.
EOF;
    }
}
