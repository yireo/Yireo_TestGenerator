<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Setup\Patch\Data;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class EncryptApiKeys implements DataPatchInterface
{
    private const PATHS = [
        'yireo_test_generator/general/openai_api_key',
        'yireo_test_generator/general/anthropic_api_key',
    ];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');
        $select = $connection->select()
            ->from($table, ['config_id', 'value'])
            ->where('path IN (?)', self::PATHS);

        foreach ($connection->fetchPairs($select) as $configId => $value) {
            $value = (string)$value;
            if ($value === '' || $this->isEncrypted($value)) {
                continue;
            }

            $connection->update(
                $table,
                ['value' => $this->encryptor->encrypt($value)],
                ['config_id = ?' => (int)$configId]
            );
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    private function isEncrypted(string $value): bool
    {
        return (bool)preg_match('/^\d+:\d+:\S+$/', $value);
    }
}
