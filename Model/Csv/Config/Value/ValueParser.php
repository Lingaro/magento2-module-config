<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Csv\Config\Value;

use Orba\Config\Model\Csv\Config\Value\Expression\AbstractExpression;

class ValueParser
{
    /** @var AbstractExpression[] */
    private array $expressions;

    /**
     * ValueParser constructor.
     * @param AbstractExpression[] $expressions
     */
    public function __construct(array $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @param string $rawValue
     * @return string | null
     */
    public function parse(string $rawValue): ?string
    {
        $value = $rawValue;
        foreach ($this->expressions as $expressionObject) {
            $matches = $expressionObject->match($rawValue);
            if ($matches === null) {
                continue;
            }

            foreach ($matches as $expressionValue => $parameter) {
                $realValue = $expressionObject->getRealValue($parameter);
                $value = $realValue !== null ? str_replace($expressionValue, $realValue, $value) : $realValue;
            }

            break;
        }

        return $value;
    }
}
