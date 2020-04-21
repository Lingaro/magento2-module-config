<?php

namespace Orba\Config\Test\Unit\Model\Csv;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Csv\Config;
use Orba\Config\Model\Csv\MultiReader;
use PHPUnit\Framework\MockObject\MockObject;

class MultiReaderTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var MultiReader */
    private $reader;

    protected function setUp()
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(MultiReader::class);
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
        $this->arguments['reader']->expects($this->exactly(count($content)))
            ->method('readConfigFile')
            ->withConsecutive(...array_map(function ($file) use ($env) {
                return [$file, $env];
            }, $paths))
            ->willReturnOnConsecutiveCalls(...array_values($content));

        $expected = [
            'key1' => $config4,
            'key2' => $config2,
            'key3' => $config3
        ];

        $this->assertSame($expected, $this->reader->readConfigFiles($paths, $env));
    }
}
