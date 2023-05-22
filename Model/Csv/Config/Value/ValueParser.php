<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config\Value;

use Lingaro\Config\Model\Csv\Config\Value\Expression\AbstractExpression;

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
