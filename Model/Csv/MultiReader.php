<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv;

use Magento\Framework\Exception\LocalizedException;

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
     * @return Config[]
     * @throws LocalizedException
     */
    public function readConfigFiles(array $paths, ?string $env = null): array
    {
        $configs = [];
        foreach ($paths as $path) {
            $configs[] = $this->reader->readConfigFile($path, $env);
        }
        return array_merge(...$configs);
    }
}
