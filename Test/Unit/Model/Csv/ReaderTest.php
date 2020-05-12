<?php

namespace Orba\Config\Test\Unit\Model\Csv;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Api\ConfigInterface;
use Orba\Config\Model\Csv\Config;
use Orba\Config\Model\Csv\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use Orba\Config\Model\MappedConfigCollection;
use Orba\Config\Model\MappedConfigCollectionFactory;

class ReaderTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var Reader */
    private $reader;

    /** @var MappedConfigCollection|MockObject */
    private $mappedConfigCollectionMock;

    protected function setUp()
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(Reader::class);

        $mappedConfigCollectionFactoryMock = $this->arguments['mappedConfigCollectionFactory'];
        $this->mappedConfigCollectionMock = $this
            ->getMockBuilder(MappedConfigCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mappedConfigCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->mappedConfigCollectionMock);

        $this->reader = $this->objectManager->getObject(Reader::class, $this->arguments);
    }

    public function testExceptionIsThrownWhenCsvCannotBeRead(): void
    {
        $path = '/path1/file2';
        $this->arguments['csv']->expects($this->once())
            ->method('getData')
            ->with($path)
            ->willThrowException(new Exception());

        $this->arguments['requiredColumnsValidator']->expects($this->never())
            ->method('validate');
        $this->arguments['configFactory']->expects($this->never())
            ->method('create');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageRegExp('/File .* can not be read/');

        $this->reader->readConfigFile($path);
    }

    public function testFileIsCorrectlyRead(): void
    {
        $env = 'env1';
        $path = '/path1/file2';
        $data = [
            [
                Config::FIELD_PATH,
                Config::FIELD_STATE,
                Config::FIELD_ENV_VALUE_PREFIX . $env
            ],
            [
                'path1',
                'always',
                'value1'
            ],
            [
                'path2',
                'init',
                'value2'
            ]
        ];

        $this->arguments['csv']->expects($this->once())
            ->method('getData')
            ->with($path)
            ->willReturn($data);

        $this->arguments['requiredColumnsValidator']->expects($this->once())
            ->method('validate')
            ->with($data);

        $config1 = $this->basicMock(ConfigInterface::class);
        $config2 = $this->basicMock(ConfigInterface::class);
        $this->arguments['configFactory']->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                [$data[0], $data[1], $env],
                [$data[0], $data[2], $env]
            )->willReturnOnConsecutiveCalls($config1, $config2);

        $this->assertSame($this->mappedConfigCollectionMock, $this->reader->readConfigFile($path, $env));
    }
}
