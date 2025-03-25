<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\UnitTest\SourceTest;

use OpenAI\Contracts\ClientContract;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Config\Config;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\AiSourceTestGeneratorInterface;
use Yireo\TestGenerator\Generator\UnitTest\SourceTest\OpenAiTestGenerator;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Utilities\OpenAiClientFactory;
use Yireo\TestGenerator\Utilities\OpenAiClientWrapper;

// @test-generator-skip-override
class OpenAiTestGeneratorTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $config = $this->createMock(Config::class);
        $clientWrapper = $this->createMock(OpenAiClientWrapper::class);
        $generator = new OpenAiTestGenerator($config, $clientWrapper);
        $this->assertInstanceOf(AiSourceTestGeneratorInterface::class, $generator);
    }

    public function testApplyReturnsFalseWhenOpenAiDisabled(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isOpenAiEnabled')->willReturn(false);

        $classStub = $this->createMock(ClassStub::class);

        $clientWrapper = $this->createMock(OpenAiClientWrapper::class);
        $generator = new OpenAiTestGenerator($config, $clientWrapper);
        $result = $generator->apply($classStub);

        $this->assertFalse($result);
    }

    public function testApplyReturnsFalseWhenApiKeyEmpty(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isOpenAiEnabled')->willReturn(true);
        $config->method('getOpenAiApiKey')->willReturn('');

        $classStub = $this->createMock(ClassStub::class);

        $clientWrapper = $this->createMock(OpenAiClientWrapper::class);
        $generator = new OpenAiTestGenerator($config, $clientWrapper);
        $result = $generator->apply($classStub);

        $this->assertFalse($result);
    }

    public function testApplyReturnsTrueWhenApiKeyNotEmpty(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('isOpenAiEnabled')->willReturn(true);
        $config->method('getOpenAiApiKey')->willReturn('test-api-key');

        $classStub = $this->createMock(ClassStub::class);

        $clientWrapper = $this->createMock(OpenAiClientWrapper::class);
        $generator = new OpenAiTestGenerator($config, $clientWrapper);
        $result = $generator->apply($classStub);

        $this->assertTrue($result);
    }

    public function testGenerate(): void
    {
        $config = $this->createMock(Config::class);

        $classStub = $this->createMock(ClassStub::class);

        $testClassStub = $this->createMock(ClassStub::class);

        $clientWrapper = $this->createMock(OpenAiClientWrapper::class);
        $clientWrapper->method('chat')->willReturn('Generated test content');

        $generator = new OpenAiTestGenerator($config, $clientWrapper);
        $result = $generator->generate($classStub, $testClassStub);

        $this->assertIsString($result);
        $this->assertEquals("Generated test content", $result);
    }

    public function testGetPrompt(): void
    {
        $config = $this->createMock(Config::class);
        $clientWrapper = $this->createMock(OpenAiClientWrapper::class);
        $generator = new OpenAiTestGenerator($config, $clientWrapper);

        $className = 'Vendor\\Package\\TestClass';
        $testClassName = 'Vendor\\Package\\Test\\TestClassTest';
        $classContents = 'class TestClass {}';

        $result = $generator->getPrompt($className, $testClassName, $classContents);

        $this->assertIsString($result);
        $this->assertStringContainsString($className, $result);
        $this->assertStringContainsString($testClassName, $result);
        $this->assertStringContainsString($classContents, $result);
        $this->assertStringContainsString('PHPUnit 9', $result);
    }

}