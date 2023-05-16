<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config\Value\Expression;

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
