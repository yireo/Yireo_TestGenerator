<?php
declare(strict_types=1);

namespace Yireo\TestGenerator\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isOpenAiEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('yireo_test_generator/general/openai_enabled');
    }

    public function getOpenAiApiKey(): string
    {
        return (string)$this->scopeConfig->getValue('yireo_test_generator/general/openai_api_key');
    }

    public function isAnthropicEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue('yireo_test_generator/general/anthropic_enabled');
    }

    public function getAnthropicApiKey(): string
    {
        return (string)$this->scopeConfig->getValue('yireo_test_generator/general/anthropic_api_key');
    }
}
