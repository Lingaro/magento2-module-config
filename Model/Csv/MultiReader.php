<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv;

use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Model\MappedConfigCollection;
use Orba\Config\Model\MappedConfigCollectionFactory;

class MultiReader
{
    /** @var Reader */
    private $reader;

    /** @var MappedConfigCollection */
    private $mappedConfigCollection;

    /**
     * MultiReader constructor.
     * @param Reader $reader
     */
    public function __construct(Reader $reader, MappedConfigCollectionFactory $mappedConfigCollectionFactory)
    {
        $this->reader = $reader;
        $this->mappedConfigCollection = $mappedConfigCollectionFactory->create();
    }

    /**
     * @param array $paths
     * @param string|null $env
     * @return MappedConfigCollection
     * @throws LocalizedException
     */
    public function readConfigFiles(array $paths, ?string $env = null): MappedConfigCollection
    {
        $collections = [];
        foreach ($paths as $path) {
            $collections[] = $this->reader->readConfigFile($path, $env);
        }
        return $this->mappedConfigCollection->mergeOtherCollections(...$collections);
    }
}
