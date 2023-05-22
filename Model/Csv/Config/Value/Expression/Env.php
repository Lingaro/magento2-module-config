<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config\Value\Expression;

use Lingaro\Config\Utils\Environment;

class Env extends AbstractExpression
{
    /** @var Environment */
    private Environment $environment;

    /**
     * File constructor.
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'env';
    }

    /**
     * @param string $value
     * @return string|null
     */
    public function getRealValue(string $value): ?string
    {
        return $this->environment->getVariable($value);
    }
}
