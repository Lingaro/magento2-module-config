<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Config;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Api\ConfigInterface;
use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Model\ResourceModel\Config\CollectionFactory;
use Orba\Config\Model\ResourceModel\Config as ConfigResourceModel;
use Orba\Config\Model\MappedConfigCollectionFactory;

/**
 * Class ConfigRepository
 * @package Orba\Config\Model\Config
 * @codeCoverageIgnore
 */
class ConfigRepository
{
    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var ConfigResourceModel */
    private $configResourceModel;

    /** @var MappedConfigCollectionFactory */
    private $mappedConfigCollectionFactory;

    /**
     * ConfigRepository constructor.
     * @param CollectionFactory $collectionFactory
     * @param ConfigResourceModel $configResourceModel
     * @param MappedConfigCollectionFactory $mappedConfigCollectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory, ConfigResourceModel $configResourceModel, MappedConfigCollectionFactory $mappedConfigCollectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
        $this->configResourceModel = $configResourceModel;
        $this->mappedConfigCollectionFactory = $mappedConfigCollectionFactory;
    }

    /**
     * @return MappedConfigCollectionInterface
     */
    public function getAllConfigs(): MappedConfigCollectionInterface
    {
        $collection = $this->collectionFactory->create();
        $mappedCollection = $this->mappedConfigCollectionFactory->create();
        /** @var ConfigInterface[] $originalItems */
        $originalItems = $collection->getItems();
        foreach ($originalItems as $config) {
            $mappedCollection->add($config);
        }
        return $mappedCollection;
    }

    /**
     * @param ConfigInterface[] $configs
     * @throws Exception
     */
    public function updateConfigs(array $configs): void
    {
        foreach ($configs as $config) {
            if (!($config instanceof ConfigInterface)) {
                throw new LocalizedException(
                    __('Wrong config object was provided to repository for update')
                );
            }
            $config->save();
        }
    }

    /**
     * @param ConfigInterface[] $configs
     */
    public function removeConfigs(array $configs): void
    {
        $this->configResourceModel->bulkRemove($configs);
    }
}
