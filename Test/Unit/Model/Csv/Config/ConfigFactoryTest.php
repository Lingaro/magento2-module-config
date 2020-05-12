<?php

namespace Orba\Config\Test\Unit\Model\Csv\Config;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Csv\Config;
use Orba\Config\Model\Csv\Config\ConfigFactory;
use Orba\Config\Model\Csv\Config\Validator\ValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;

class ConfigFactoryTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var ConfigFactory */
    private $factory;

    protected function setUp()
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(ConfigFactory::class);
    }

    /**
     * @param array $headers
     * @param array $values
     * @param array $data
     * @param string $valueColumn
     * @param string|null $env
     * @throws LocalizedException
     *
     * @dataProvider correctCsvLineProvider
     */
    public function testFactoryCreatesConfigCorrectlyWithPassedValidation(
        array $headers,
        array $values,
        array $data,
        string $valueColumn,
        ?string $env
    ): void {
        $this->arguments['configValidators'] = [];
        $this->arguments['configValidators'][0] = $this->basicMock(ValidatorInterface::class);
        $this->arguments['configValidators'][0]->expects($this->once())
            ->method('validate');
        $this->arguments['configValidators'][1] = $this->basicMock(ValidatorInterface::class);
        $this->arguments['configValidators'][1]->expects($this->once())
            ->method('validate');

        $this->arguments['valueParser']->expects($this->once())
            ->method('parse')
            ->with($data[$valueColumn])
            ->willReturn($data[$valueColumn]);

        $this->arguments['valueGetter']->expects($this->once())
            ->method('getValueWithBackendModel')
            ->with($data[Config::FIELD_PATH], $data[$valueColumn])
            ->willReturn($data[$valueColumn]);

        $this->factory = $this->objectManager->getObject(ConfigFactory::class, $this->arguments);

        $data[Config::FIELD_VALUE] = $data[$valueColumn];
        $expected = new Config($data);

        $this->assertEquals($expected, $this->factory->create($headers, $values, $env));
    }

    /**
     * @param array $headers
     * @param array $values
     * @param string|null $env
     * @throws LocalizedException
     *
     * @dataProvider incorrectCsvLineProvider
     */
    public function testFactoryThrowsExceptionWhenValueColumnDoesntExist(
        array $headers,
        array $values,
        ?string $env
    ): void {
        $this->arguments['configValidators'] = [];
        $this->arguments['valueParser']->expects($this->never())
            ->method('parse');
        $this->factory = $this->objectManager->getObject(ConfigFactory::class, $this->arguments);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageRegExp('/Value column .* does not exist/');

        $this->factory->create($headers, $values, $env);
    }

    /**
     * Provide sets of correct csv line
     *
     * @return array
     */
    public function correctCsvLineProvider(): array
    {
        return [
            'line with default value' => [
                [
                    Config::FIELD_PATH,
                    Config::FIELD_SCOPE,
                    Config::FIELD_STATE,
                    Config::FIELD_VALUE
                ],
                [
                    'value1',
                    'value2',
                    'value3',
                    'value4'
                ],
                [
                    Config::FIELD_PATH => 'value1',
                    Config::FIELD_SCOPE => 'value2',
                    Config::FIELD_STATE => 'value3',
                    Config::FIELD_VALUE => 'value4'
                ],
                Config::FIELD_VALUE,
                null
            ],
            'line with env value' => [
                [
                    Config::FIELD_PATH,
                    Config::FIELD_SCOPE,
                    Config::FIELD_STATE,
                    Config::FIELD_VALUE,
                    Config::FIELD_ENV_VALUE_PREFIX . 'env1'
                ],
                [
                    'value1',
                    'value2',
                    'value3',
                    'value4',
                    'value5',
                ],
                [
                    Config::FIELD_PATH => 'value1',
                    Config::FIELD_SCOPE => 'value2',
                    Config::FIELD_STATE => 'value3',
                    Config::FIELD_VALUE => 'value4',
                    Config::FIELD_ENV_VALUE_PREFIX . 'env1' => 'value5'
                ],
                Config::FIELD_ENV_VALUE_PREFIX . 'env1',
                'env1'
            ]
        ];
    }

    /**
     * Provide sets of incorrect csv line
     *
     * @return array
     */
    public function incorrectCsvLineProvider(): array
    {
        return [
            'default value does not exist' => [
                [
                    Config::FIELD_PATH,
                    Config::FIELD_STATE
                ],
                [
                    'value1',
                    'value2'
                ],
                null
            ],
            'env value does not exist' => [
                [
                    Config::FIELD_PATH,
                    Config::FIELD_STATE,
                    Config::FIELD_VALUE
                ],
                [
                    'value1',
                    'value2',
                    'value3'
                ],
                'env1'
            ]
        ];
    }
}
