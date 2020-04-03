<?php

namespace Orba\Config\Model\Csv\Config\Value\Expression;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File as DriverFile;

class File extends AbstractExpression
{
    /** @var DriverFile */
    private $driverFile;

    /**
     * File constructor.
     * @param DriverFile $driverFile
     */
    public function __construct(DriverFile $driverFile)
    {
        $this->driverFile = $driverFile;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'file';
    }

    /**
     * @param string $value
     * @return string
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function getRealValue(string $value): string
    {
        if (!$this->driverFile->isReadable($value)) {
            throw new LocalizedException(
                __('File %1 can not be read', $value)
            );
        }
        return $this->driverFile->fileGetContents($value);
    }
}
