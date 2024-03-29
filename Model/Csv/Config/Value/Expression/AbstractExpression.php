<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config\Value\Expression;

/**
 * Class AbstractExpression
 * @package Lingaro\Config\Model\Csv\Config\Value\Expression
 * @codeCoverageIgnore
 */
abstract class AbstractExpression
{
    protected const BASE_EXPRESSION = '/\{\{NAME\s?([^\s\}]*)\}\}/';

    /**
     * @param string $rawValue
     * @return array|null
     */
    public function match(string $rawValue): ?array
    {
        $matches = [];
        $matchesNumber = preg_match_all($this->getExpression(), $rawValue, $matches);
        if ($matchesNumber === 0) {
            return null;
        }

        if (count($matches) !== 2) {
            return null;
        }

        return array_combine($matches[0], $matches[1]);
    }

    /**
     * @return string
     */
    public function getExpression(): string
    {
        return str_replace('NAME', $this->getName(), self::BASE_EXPRESSION);
    }

    /**
     * @return string
     */
    abstract protected function getName(): string;

    /**
     * @param string $value
     * @return string|null
     */
    abstract public function getRealValue(string $value): ?string;
}
