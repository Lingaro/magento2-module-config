<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv;

use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Model\Csv\Config\ConfigCollection;

class MultiReader
{
    /** @var Reader */
    private $reader;

    /**
     * MultiReader constructor.
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param array $paths
     * @param string|null $env
     * @return ConfigCollection
     * @throws LocalizedException
     */
    public function readConfigFiles(array $paths, ?string $env = null): ConfigCollection
    {
        $configs = [];
        foreach ($paths as $path) {
            $configs[] = $this->reader->readConfigFile($path, $env)->getAll();
        }
        return new ConfigCollection(array_merge(...$configs));
    }
}
