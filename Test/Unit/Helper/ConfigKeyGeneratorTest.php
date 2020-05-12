<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Api\ConfigInterface;
use Orba\Config\Helper\ConfigKeyGenerator;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigKeyGeneratorTest extends BaseTestCase
{
    /** @var ConfigKeyGenerator */
    private $generator;

    protected function setUp()
    {
        parent::setUp();
        $this->generator = $this->objectManager->getObject(ConfigKeyGenerator::class);
    }

    /**
     * @param string $path
     * @param string $scope
     * @param string|null $code
     * @param string $expectedKey
     *
     * @dataProvider csvDataProvider
     */
    public function testGeneratorGeneratesCorrectKeyForCsv(
        string $path,
        string $scope,
        ?string $code,
        string $expectedKey
    ): void {
        /** @var Config|MockObject $configMock */
        $configMock = $this->basicMock(ConfigInterface::class);
        $configMock->expects($this->once())
            ->method('getPath')
            ->willReturn($path);
        $configMock->expects($this->once())
            ->method('getScopeType')
            ->willReturn($scope);
        $configMock->expects($this->once())
            ->method('getScopeId')
            ->willReturn($code);
        $configMock->expects($this->never())
            ->method('getValue');

        $generatedKey = $this->generator->generateKey($configMock);

        $this->assertEquals($expectedKey, $generatedKey);
    }

    /**
     * Provide data for testing CSV key generation
     *
     * @return array
     */
    public function csvDataProvider(): array
    {
        return [
            'all data is available' => [
                'config/path', 'default', 2, 'config/path|default|2'
            ],
            'code is null' => [
                'config/path', 'default', null, 'config/path|default|'
            ]
        ];
    }
}
