<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Csv\Config\Value\Expression;

class NullExpr extends AbstractExpression
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'null';
    }

    /**
     * @param string $value
     * @return string|null
     */
    public function getRealValue(string $value): ?string
    {
        return null;
    }
}
