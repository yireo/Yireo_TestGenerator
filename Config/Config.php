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
}
