<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Test\Unit\Model\Csv;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Lingaro\Config\Model\Csv\Config;
use Lingaro\Config\Model\Csv\MultiReader;
use PHPUnit\Framework\MockObject\MockObject;
use Lingaro\Config\Model\MappedConfigCollection;
use Lingaro\Config\Model\MappedConfigCollectionFactory;

class MultiReaderTest extends BaseTestCase
{
    /** @var MockObject[] */
    private array $arguments;

    /** @var MultiReader */
    private MultiReader $reader;

    /** @var MappedConfigCollection|MockObject */
    private MockObject $mappedConfigCollectionMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(MultiReader::class);

        $mappedConfigCollectionFactoryMock = $this->arguments['mappedConfigCollectionFactory'];
        $this->mappedConfigCollectionMock = $this
            ->getMockBuilder(MappedConfigCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mappedConfigCollectionFactoryMock
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->mappedConfigCollectionMock);

        $this->reader = $this->objectManager->getObject(MultiReader::class, $this->arguments);
    }

    public function testUseSingleReaderToReadManyFilesAndMergeResultsCorrectly(): void
    {
        $env = 'env1';
        $config1 = $this->basicMock(Config::class);
        $config2 = $this->basicMock(Config::class);
        $config3 = $this->basicMock(Config::class);
        $config4 = $this->basicMock(Config::class);
        $content = [
            'file1' => [
                'key1' => $config1,
                'key2' => $config2
            ],
            'file2' => [
                'key3' => $config3,
                'key1' => $config4,
            ]
        ];
        $paths = array_keys($content);
        $this->arguments['reader']
            ->expects($this->exactly(count($content)))
            ->method('readConfigFile')
            ->willReturn($this->mappedConfigCollectionMock);

        $this->mappedConfigCollectionMock
            ->expects($this->any())
            ->method('mergeOtherCollections')
            ->willReturnSelf();

        $this->assertSame($this->mappedConfigCollectionMock, $this->reader->readConfigFiles($paths, $env));
    }
}
