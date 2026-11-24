<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\UnitTest\SourceTest;

use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Config\Config;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\AiOutputValidator;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\AnthropicAiTestGenerator;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Utilities\AnthropicClientWrapper;

class AnthropicAiTestGeneratorTest extends TestCase
{
    public function testApplyWhenAnthropicIsDisabled(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('isAnthropicEnabled')
            ->willReturn(false);

        $classStub = $this->createMock(ClassStub::class);

        $clientWrapper = $this->createMock(AnthropicClientWrapper::class);

        $generator = new AnthropicAiTestGenerator($config, $clientWrapper, new AiOutputValidator());
        $result = $generator->apply($classStub);

        $this->assertFalse($result);
    }

    public function testApplyWhenAnthropicIsEnabledButNoApiKey(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('isAnthropicEnabled')
            ->willReturn(true);
        $config->expects($this->once())
            ->method('getAnthropicApiKey')
            ->willReturn('');

        $classStub = $this->createMock(ClassStub::class);
        $clientWrapper = $this->createMock(AnthropicClientWrapper::class);

        $generator = new AnthropicAiTestGenerator($config, $clientWrapper, new AiOutputValidator());
        $result = $generator->apply($classStub);

        $this->assertFalse($result);
    }

    public function testApplyWhenAnthropicIsEnabledWithApiKey(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->once())
            ->method('isAnthropicEnabled')
            ->willReturn(true);
        $config->expects($this->once())
            ->method('getAnthropicApiKey')
            ->willReturn('test-api-key');

        $classStub = $this->createMock(ClassStub::class);
        $clientWrapper = $this->createMock(AnthropicClientWrapper::class);

        $generator = new AnthropicAiTestGenerator($config, $clientWrapper, new AiOutputValidator());
        $result = $generator->apply($classStub);

        $this->assertTrue($result);
    }

    public function testGenerate(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getAnthropicModel')->willReturn('test-model');

        $classStub = $this->createMock(ClassStub::class);
        $testClassStub = $this->createMock(ClassStub::class);
        $testClassStub->method('getFullQualifiedClassName')->willReturn('Vendor\\Module\\Test\\Unit\\FooTest');

        $clientWrapper = $this->createMock(AnthropicClientWrapper::class);
        $clientWrapper->method('chat')
            ->with($this->isString(), $this->isString(), 'test-model')
            ->willReturn("Sure:\n```php\n<?php\nnamespace Vendor\\Module\\Test\\Unit;\n\nfinal class FooTest {}\n```\nDone.");

        $generator = new AnthropicAiTestGenerator($config, $clientWrapper, new AiOutputValidator());
        $output = $generator->generate($classStub, $testClassStub);

        $this->assertEquals("<?php\nnamespace Vendor\\Module\\Test\\Unit;\n\nfinal class FooTest {}", $output);
    }

    public function testGetPrompt(): void
    {
        $className = 'TestNamespace\\TestClass';
        $testClassName = 'TestNamespace\\Test\\TestClassTest';
        $classContents = '<?php class TestClass {}';

        $config = $this->createMock(Config::class);

        $clientWrapper = $this->createMock(AnthropicClientWrapper::class);

        $generator = new AnthropicAiTestGenerator($config, $clientWrapper, new AiOutputValidator());

        $prompt = $generator->getPrompt($className, $testClassName, $classContents);

        $this->assertStringContainsString($className, $prompt);
        $this->assertStringContainsString($testClassName, $prompt);
        $this->assertStringContainsString($classContents, $prompt);
        $this->assertStringContainsString('', $prompt);
        $this->assertStringContainsString('declare(strict_types=1)', $prompt);
    }
}
