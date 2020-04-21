<?php

namespace Orba\Config\Model\Csv\Config\Value\Expression;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File as DriverFile;

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
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function getRealValue(string $value): ?string
    {
        return null;
    }
}
