<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Test\Unit\Model\Csv\Config\Value\Expression;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Csv\Config\Value\Expression\Env;
use PHPUnit\Framework\MockObject\MockObject;

class EnvTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var Env */
    private $env;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(Env::class);
        $this->env = $this->objectManager->getObject(Env::class, $this->arguments);
    }

    public function testNameIsCorrect(): void
    {
        $this->assertEquals('env', $this->env->getName());
    }

    public function testRealValueIsCorrect(): void
    {
        $name = 'TEST_ENV';
        $value = 'value';
        $this->arguments['environment']->expects($this->once())
            ->method('getVariable')
            ->with($name)
            ->willReturn($value);

        $this->assertEquals($value, $this->env->getRealValue($name));
    }

    /**
     * @param string $rawValue
     * @param array $expectedValue
     *
     * @dataProvider envVariablesDataProvider
     */
    public function testExpressionIsMatchedCorrectly(
        string $rawValue,
        array $expectedValue
    ): void {
        $matched = $this->env->match($rawValue);
        $this->assertEquals($expectedValue, $matched);
    }

    /**
     * Provide data for env matching operation
     *
     * @return array
     */
    public function envVariablesDataProvider(): array
    {
        return [
            'only one existing expression' => [
                '{{env TEST_VAR}}',
                ['{{env TEST_VAR}}' => 'TEST_VAR']
            ],
            'one existing expression with additional chars' => [
                'prefix{{env TEST_VAR}}suffix',
                ['{{env TEST_VAR}}' => 'TEST_VAR']
            ],
            'one expression existing two times with additional chars' => [
                'prefix{{env TEST_VAR}}suffix{{env TEST_VAR}}suffix2',
                ['{{env TEST_VAR}}' => 'TEST_VAR']
            ],
            'two different existing expression with additional chars' => [
                'prefix{{env TEST_VAR1}}suffix{{env TEST_VAR2}}suffix2',
                ['{{env TEST_VAR1}}' => 'TEST_VAR1', '{{env TEST_VAR2}}' => 'TEST_VAR2']
            ]
        ];
    }
}
