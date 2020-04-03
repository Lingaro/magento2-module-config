<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv\Config\Value\Expression;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use Orba\Config\Utils\Environment;

class Env extends AbstractExpression
{
    /** @var Environment */
    private $environment;

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
     * @return string
     */
    public function getRealValue(string $value): string
    {
        return $this->environment->getVariable($value);
    }
}
