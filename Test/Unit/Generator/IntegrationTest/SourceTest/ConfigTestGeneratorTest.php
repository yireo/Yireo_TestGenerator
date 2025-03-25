<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator\IntegrationTest\SourceTest;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\IntegrationTest\SourceTest\ConfigTestGenerator;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Model\ClassStub;
use Yireo\TestGenerator\Test\Unit\PhpGeneratorStubFactory;

class ConfigTestGeneratorTest extends TestCase
{
    public function testApply(): void
    {
        $phpGeneratorFactory = new PhpGeneratorFactory(
            $this->createMock(DirectoryList::class),
            $this->createMock(Filesystem::class),
        );

        $configTestGenerator = new ConfigTestGenerator($phpGeneratorFactory);
        
        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getFullQualifiedClassName')
            ->willReturn('Vendor\Module\Config\Config');
        
        $this->assertTrue($configTestGenerator->apply($classStubMock));
        
        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getFullQualifiedClassName')
            ->willReturn('Vendor\Module\Other\Class');
        
        $this->assertFalse($configTestGenerator->apply($classStubMock));
    }

    public function testGenerate(): void
    {
        $phpGenerator = (new PhpGeneratorStubFactory())->create(
            'Dummy',
            __NAMESPACE__,
            $this->createMock(WriteInterface::class)
        );

        $phpGeneratorFactory = $this->createMock(PhpGeneratorFactory::class);
        $phpGeneratorFactory->method('create')->willReturn($phpGenerator);

        $configTestGenerator = new ConfigTestGenerator($phpGeneratorFactory);

        $classStubMock = $this->createMock(ClassStub::class);
        $classStubMock->method('getFullQualifiedClassName')->willReturn(__CLASS__);

        $testClassStubMock = $this->createMock(ClassStub::class);
        $testClassStubMock->method('getFullQualifiedClassName')->willReturn(__CLASS__);

        $result = $configTestGenerator->generate($classStubMock, $testClassStubMock);
        $this->assertStringContainsString('ConfigFixture', $result->output());
    }
}