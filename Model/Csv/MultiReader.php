<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv;

use Magento\Framework\Exception\LocalizedException;
use Lingaro\Config\Model\MappedConfigCollection;
use Lingaro\Config\Model\MappedConfigCollectionFactory;

class MultiReader
{
    /** @var Reader */
    private Reader $reader;

    /** @var MappedConfigCollection */
    private MappedConfigCollection $mappedConfigCollection;

    /**
     * MultiReader constructor.
     * @param Reader $reader
     * @param MappedConfigCollectionFactory $mappedConfigCollectionFactory
     */
    public function __construct(
        Reader $reader,
        MappedConfigCollectionFactory $mappedConfigCollectionFactory
    ){
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
