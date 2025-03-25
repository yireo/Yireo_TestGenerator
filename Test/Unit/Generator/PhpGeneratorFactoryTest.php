<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Test\Unit\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\TestGenerator\Generator\PhpGenerator;
use Yireo\TestGenerator\Generator\PhpGeneratorFactory;
use Yireo\TestGenerator\Model\ClassStub;

// @test-generator-skip-override
class PhpGeneratorFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreate(): void
    {
        // Mock DirectoryList
        /** @var DirectoryList|MockObject $directoryListMock */
        $directoryListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock WriteInterface
        /** @var WriteInterface|MockObject $writerMock */
        $writerMock = $this->getMockBuilder(WriteInterface::class)
            ->getMock();

        // Mock Filesystem
        /** @var Filesystem|MockObject $filesystemMock */
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($writerMock);

        // Mock ClassStub
        /** @var ClassStub|MockObject $classStubMock */
        $classStubMock = $this->getMockBuilder(ClassStub::class)
            ->disableOriginalConstructor()
            ->getMock();
        $classStubMock->expects($this->once())
            ->method('getClassName')
            ->willReturn('TestClass');
        $classStubMock->expects($this->once())
            ->method('getNamespace')
            ->willReturn('Test\Namespace');

        // Create instance of PhpGeneratorFactory
        $factory = new PhpGeneratorFactory($directoryListMock, $filesystemMock);

        // Execute the create method
        $result = $factory->create($classStubMock);

        // Assert the return type
        $this->assertInstanceOf(PhpGenerator::class, $result);
    }

    /**
     * @return void
     */
    public function testCreateWithDifferentClassStub(): void
    {
        // Mock DirectoryList
        /** @var DirectoryList|MockObject $directoryListMock */
        $directoryListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock WriteInterface
        /** @var WriteInterface|MockObject $writerMock */
        $writerMock = $this->getMockBuilder(WriteInterface::class)
            ->getMock();

        // Mock Filesystem
        /** @var Filesystem|MockObject $filesystemMock */
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($writerMock);

        // Mock ClassStub with different values
        /** @var ClassStub|MockObject $classStubMock */
        $classStubMock = $this->getMockBuilder(ClassStub::class)
            ->disableOriginalConstructor()
            ->getMock();
        $classStubMock->expects($this->once())
            ->method('getClassName')
            ->willReturn('AnotherTestClass');
        $classStubMock->expects($this->once())
            ->method('getNamespace')
            ->willReturn('Another\Test\Namespace');

        // Create instance of PhpGeneratorFactory
        $factory = new PhpGeneratorFactory($directoryListMock, $filesystemMock);

        // Execute the create method
        $result = $factory->create($classStubMock);

        // Assert the return type
        $this->assertInstanceOf(PhpGenerator::class, $result);
    }
}
