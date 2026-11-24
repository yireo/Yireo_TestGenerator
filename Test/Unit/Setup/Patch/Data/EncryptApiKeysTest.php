<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Setup\Patch\Data\EncryptApiKeys;

// @test-generator-skip-override
class EncryptApiKeysTest extends TestCase
{
    public function testApplyEncryptsOnlyPlaintextValues(): void
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();

        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchPairs')->with($select)->willReturn([
            1 => 'sk-plain-key',
            2 => '0:3:alreadyEncryptedValue==',
            3 => '',
            4 => '1:2:another+encrypted/value',
            5 => 'sk-ant-1:2:3',
        ]);

        $updates = [];
        $connection->method('update')->willReturnCallback(
            function (string $table, array $bind, array $where) use (&$updates): int {
                $updates[] = [$table, $bind, $where];
                return 1;
            }
        );

        $setup = $this->createMock(ModuleDataSetupInterface::class);
        $setup->method('getConnection')->willReturn($connection);
        $setup->method('getTable')->with('core_config_data')->willReturn('core_config_data');

        $encryptor = $this->createMock(EncryptorInterface::class);
        $encryptor->method('encrypt')->willReturnCallback(fn (string $value): string => 'encrypted:'.$value);

        (new EncryptApiKeys($setup, $encryptor))->apply();

        $this->assertSame([
            ['core_config_data', ['value' => 'encrypted:sk-plain-key'], ['config_id = ?' => 1]],
            ['core_config_data', ['value' => 'encrypted:sk-ant-1:2:3'], ['config_id = ?' => 5]],
        ], $updates);
    }
}
