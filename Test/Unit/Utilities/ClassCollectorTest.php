<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Utilities;

use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Utilities\ClassCollector;

// @test-generator-skip-override
class ClassCollectorTest extends TestCase
{
    /**
     * @test
     */
    public function testCollect(): void
    {
        $tempDir = sys_get_temp_dir() . '/class-collector-test-' . uniqid();
        mkdir($tempDir, 0777, true);

        // Create a test PHP file with a class
        $classFile = $tempDir . '/TestClass.php';
        file_put_contents($classFile, '<?php
namespace Test\Namespace;

class TestClass
{
}
');

        // Create a test file in a Test directory that should be skipped
        mkdir($tempDir . '/Test', 0777, true);
        $testFile = $tempDir . '/Test/SkipThis.php';
        file_put_contents($testFile, '<?php
namespace Test\Namespace\Test;

class SkipThis
{
}
');

        // Create a PHP file without a class that should be skipped
        $noClassFile = $tempDir . '/NoClass.php';
        file_put_contents($noClassFile, '<?php
// This file has no class
$var = "test";
');

        try {
            $classCollector = new ClassCollector();
            $result = $classCollector->collect($tempDir);

            $this->assertIsArray($result);
            $this->assertCount(1, $result);
            $this->assertArrayHasKey($classFile, $result);
            $this->assertEquals('Test\Namespace\\TestClass', $result[$classFile]);
        } finally {
            // Cleanup
            @unlink($classFile);
            @unlink($testFile);
            @unlink($noClassFile);
            @rmdir($tempDir . '/Test');
            @rmdir($tempDir);
        }
    }

    /**
     * @test
     */
    public function testCollectWithEmptyDirectory(): void
    {
        $tempDir = sys_get_temp_dir() . '/class-collector-empty-test-' . uniqid();
        mkdir($tempDir, 0777, true);

        try {
            $classCollector = new ClassCollector();
            $result = $classCollector->collect($tempDir);

            $this->assertIsArray($result);
            $this->assertEmpty($result);
        } finally {
            @rmdir($tempDir);
        }
    }

    /**
     * @test
     */
    public function testCollectWithInvalidDirectory(): void
    {
        $this->expectException(\RuntimeException::class);

        $tempDir = sys_get_temp_dir() . '/non-existent-directory-' . uniqid();

        $classCollector = new ClassCollector();
        $classCollector->collect($tempDir);
    }
}
