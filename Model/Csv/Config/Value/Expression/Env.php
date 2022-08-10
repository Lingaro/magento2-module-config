<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Csv\Config\Value\Expression;

use Orba\Config\Utils\Environment;

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
