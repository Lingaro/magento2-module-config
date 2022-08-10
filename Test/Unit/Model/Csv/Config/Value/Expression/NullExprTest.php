<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Test\Unit\Model\Csv\Config\Value\Expression;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Csv\Config\Value\Expression\NullExpr;
use PHPUnit\Framework\MockObject\MockObject;

class NullExprTest extends BaseTestCase
{
    /** @var MockObject[] */
    private array $arguments;

    /** @var NullExpr */
    private NullExpr $nullExpr;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(NullExpr::class);
        $this->nullExpr = $this->objectManager->getObject(NullExpr::class, $this->arguments);
    }

    /**
     * @return void
     */
    public function testNameIsCorrect(): void
    {
        $this->assertEquals('null', $this->nullExpr->getName());
    }

    /**
     * @return void
     */
    public function testRealValueIsReadForReadableFile(): void
    {
        $name = '';
        $this->assertNull($this->nullExpr->getRealValue($name));
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
        $matched = $this->nullExpr->match($rawValue);
        $this->assertEquals($expectedValue, $matched);
    }

    /**
     * Provide data for file matching operation
     *
     * @return array
     */
    public function envVariablesDataProvider(): array
    {
        return [
            'only one existing expression' => [
                '{{null}}',
                ['{{null}}' => '']
            ],
            'one existing expression with additional chars' => [
                'prefix{{null}}suffix',
                ['{{null}}' => '']
            ],
            'one expression existing two times with additional chars' => [
                'prefix{{null}}suffix{{null}}suffix2',
                ['{{null}}' => '']
            ]
        ];
    }
}
