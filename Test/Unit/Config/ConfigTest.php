<?php
declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Config\Config;

// @test-generator-skip-override
class ConfigTest extends TestCase
{
    /**
     * @return void
     */
    public function testIsOpenAiEnabled(): void
    {
        // Test case for enabled
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/openai_enabled', '1');
        $config = new Config($scopeConfigMock);
        $this->assertTrue($config->isOpenAiEnabled());

        // Test case for disabled
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/openai_enabled', '0');
        $config = new Config($scopeConfigMock);
        $this->assertFalse($config->isOpenAiEnabled());
    }

    /**
     * @return void
     */
    public function testGetOpenAiApiKey(): void
    {
        $apiKey = 'test-api-key-12345';
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/openai_api_key', $apiKey);
        $config = new Config($scopeConfigMock);
        $this->assertEquals($apiKey, $config->getOpenAiApiKey());

        // Test empty key
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/openai_api_key', '');
        $config = new Config($scopeConfigMock);
        $this->assertEquals('', $config->getOpenAiApiKey());
    }

    /**
     * @return void
     */
    public function testIsAnthropicEnabled(): void
    {
        // Test case for enabled
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/anthropic_enabled', '1');
        $config = new Config($scopeConfigMock);
        $this->assertTrue($config->isAnthropicEnabled());

        // Test case for disabled
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/anthropic_enabled', '0');
        $config = new Config($scopeConfigMock);
        $this->assertFalse($config->isAnthropicEnabled());
    }

    /**
     * @return void
     */
    public function testGetAnthropicApiKey(): void
    {
        $apiKey = 'anthropic-api-key-67890';
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/anthropic_api_key', $apiKey);
        $config = new Config($scopeConfigMock);
        $this->assertEquals($apiKey, $config->getAnthropicApiKey());

        // Test empty key
        $scopeConfigMock = $this->createScopeConfigMock('yireo_test_generator/general/anthropic_api_key', '');
        $config = new Config($scopeConfigMock);
        $this->assertEquals('', $config->getAnthropicApiKey());
    }

    /**
     * Create a mock of ScopeConfigInterface
     *
     * @param string $path
     * @param string $returnValue
     * @return ScopeConfigInterface|MockObject
     */
    private function createScopeConfigMock(string $path, string $returnValue): ScopeConfigInterface
    {
        /** @var ScopeConfigInterface|MockObject $scopeConfigMock */
        $scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $scopeConfigMock->method('getValue')
            ->with($path)
            ->willReturn($returnValue);

        return $scopeConfigMock;
    }
}
