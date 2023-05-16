<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Helper\ConfigKeyGenerator;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigKeyGeneratorTest extends BaseTestCase
{
    /** @var ConfigKeyGenerator */
    private $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = $this->objectManager->getObject(ConfigKeyGenerator::class);
    }

    /**
     * @param string $path
     * @param string $scope
     * @param int|null $code
     * @param string $expectedKey
     *
     * @dataProvider csvDataProvider
     */
    public function testGeneratorGeneratesCorrectKeyForCsv(
        string $path,
        string $scope,
        ?int $code,
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
