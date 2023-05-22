<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Test\Unit\Model\Csv;

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Magento\Store\Model\ScopeInterface;
use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Model\Config\ConfigFactory;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigFactoryTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var ConfigFactory */
    private $factory;

    /** @var MockObject | Config  */
    private $createdConfigMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(ConfigFactory::class);
        $this->createdConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setDataByPath',
                    'setStore',
                    'setWebsite'
                ]
            )->getMock();
        $this->arguments['originalConfigFactory']->expects($this->once())
            ->method('create')
            ->willReturn($this->createdConfigMock);
        $this->factory = $this->objectManager->getObject(ConfigFactory::class, $this->arguments);
    }

    /**
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string|null $code
     *
     * @dataProvider configDataProvider
     */
    public function testFactoryReturnsCorrectObjectForConfig(
        string $path,
        string $value,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $code = null
    ): void {
        $configMock = $this->prepareConfigMock($path, $value, $scope, $code);
        $this->createdConfigMock->expects($this->once())
            ->method('setDataByPath')
            ->with($path, $value);
        switch ($scope) {
            case ScopeInterface::SCOPE_STORES:
                $this->createdConfigMock->expects($this->once())
                    ->method('setStore')
                    ->with($code);
                break;
            case ScopeInterface::SCOPE_WEBSITES:
                $this->createdConfigMock->expects($this->once())
                    ->method('setWebsite')
                    ->with($code);
                break;
        }

        $this->assertEquals($this->createdConfigMock, $this->factory->create($configMock));
    }

    /**
     * Provide data with single config values
     *
     * @return array
     */
    public function configDataProvider(): array
    {
        return [
            'path + value' => [
                'path/to/config',
                'value'
            ],
            'path + value + wrong scope type' => [
                'path/to/config',
                'value',
                'some scope'
            ],
            'path + value + website scope + scope code' => [
                'path/to/config',
                'value',
                ScopeInterface::SCOPE_WEBSITES,
                'poland'
            ],
            'path + value + store scope + scope code' => [
                'path/to/config',
                'value',
                ScopeInterface::SCOPE_STORES,
                'pl'
            ]
        ];
    }

    /**
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param string|null $code
     * @return MockObject
     */
    private function prepareConfigMock(
        string $path,
        string $value,
        string $scope,
        ?string $code
    ): MockObject {
        $configMock = $this->basicMock(ConfigInterface::class);
        $configMock->method('getPath')
            ->willReturn($path);
        $configMock->method('getValue')
            ->willReturn($value);
        $configMock->method('getScopeType')
            ->willReturn($scope);
        $configMock->method('getScopeCode')
            ->willReturn($code);

        return $configMock;
    }
}
