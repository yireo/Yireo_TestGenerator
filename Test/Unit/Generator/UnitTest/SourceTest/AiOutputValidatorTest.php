<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\UnitTest\SourceTest;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\AiOutputValidator;

// @test-generator-skip-override
class AiOutputValidatorTest extends TestCase
{
    private const EXPECTED_CLASS = 'Vendor\\Module\\Test\\Unit\\FooTest';
    private const VALID_CODE = "<?php declare(strict_types=1);\n\nnamespace Vendor\\Module\\Test\\Unit;\n\nfinal class FooTest {}";

    #[DataProvider('getValidResponses')]
    public function testValidateReturnsCode(string $response): void
    {
        $this->assertSame(self::VALID_CODE, (new AiOutputValidator())->validate($response, '\\'.self::EXPECTED_CLASS));
    }

    public static function getValidResponses(): array
    {
        return [
            'plain' => [self::VALID_CODE],
            'whitespace' => ["\n  ".self::VALID_CODE."\n\n"],
            'fenced' => ["```php\n".self::VALID_CODE."\n```"],
            'fenced with chatter' => ["Here you go:\n\n```php\n".self::VALID_CODE."\n```\n\n```php\n<?php echo 1;\n```"],
            'fenced without language' => ["```\n".self::VALID_CODE."\n```"],
        ];
    }

    #[DataProvider('getInvalidResponses')]
    public function testValidateRejectsResponse(string $response, string $message): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        (new AiOutputValidator())->validate($response, self::EXPECTED_CLASS);
    }

    public static function getInvalidResponses(): array
    {
        return [
            'empty' => ['', 'does not contain PHP code'],
            'refusal' => ['I cannot help with that.', 'does not contain PHP code'],
            'syntax error' => ["<?php\nnamespace Vendor\\Module\\Test\\Unit;\nclass FooTest {", 'invalid PHP'],
            'wrong namespace' => ["<?php\nnamespace Evil\\Test;\nclass FooTest {}", 'namespace "Vendor\\Module\\Test\\Unit"'],
            'namespace prefix only' => ["<?php\nnamespace Vendor\\Module\\Test\\UnitX;\nclass FooTest {}", 'namespace'],
            'wrong class' => ["<?php\nnamespace Vendor\\Module\\Test\\Unit;\nclass BarTest {}", 'class "FooTest"'],
            'class name prefix only' => ["<?php\nnamespace Vendor\\Module\\Test\\Unit;\nclass FooTestEvil {}", 'class "FooTest"'],
        ];
    }

    public function testValidateRejectsClassWithoutNamespace(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('has no namespace');

        (new AiOutputValidator())->validate("<?php\nclass FooTest {}", 'FooTest');
    }
}
