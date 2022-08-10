<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Test\Unit\Model\Csv\Config\Value;

use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Csv\Config\Value\Expression\AbstractExpression;
use Orba\Config\Model\Csv\Config\Value\ValueParser;
use PHPUnit\Framework\MockObject\MockObject;

class ValueParserTest extends BaseTestCase
{
    /** @var MockObject[] */
    private array $arguments;

    /** @var ValueParser */
    private ValueParser $parser;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(ValueParser::class);
    }

    /**
     * @return void
     */
    public function testParseRawDataCorrectly(): void
    {
        $rawValue = 'test(A)test(B)test';
        $expected = 'testV1testV2test';

        $expressions = [];
        $expressions[0] = $this->basicMock(AbstractExpression::class);
        $expressions[0]->expects($this->once())
            ->method('match')
            ->with($rawValue)
            ->willReturn(null);
        $expressions[0]->expects($this->never())
            ->method('getRealValue');
        $matches = [
            '(A)' => 'A',
            '(B)' => 'B'
        ];
        $expressions[1] = $this->basicMock(AbstractExpression::class);
        $expressions[1]->expects($this->once())
            ->method('match')
            ->with($rawValue)
            ->willReturn($matches);
        $expressions[1]->expects($this->exactly(2))
            ->method('getRealValue')
            ->withConsecutive(['A'], ['B'])
            ->willReturnOnConsecutiveCalls('V1', 'V2');
        $this->arguments['expressions'] = $expressions;

        $this->parser = $this->objectManager->getObject(ValueParser::class, $this->arguments);

        $this->assertEquals($expected, $this->parser->parse($rawValue));
    }
}
