<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Config;

use Exception;
use Orba\Config\Model\Config;
use Orba\Config\Model\ResourceModel\Config\CollectionFactory;
use Orba\Config\Model\ResourceModel\Config as ConfigResourceModel;

class ConfigRepository
{
    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var ConfigResourceModel */
    private $configResourceModel;

    /**
     * ConfigRepository constructor.
     * @param CollectionFactory $collectionFactory
     * @param ConfigResourceModel $configResourceModel
     */
    public function __construct(CollectionFactory $collectionFactory, ConfigResourceModel $configResourceModel)
    {
        $this->collectionFactory = $collectionFactory;
        $this->configResourceModel = $configResourceModel;
    }

    /**
     * @return Config[]
     */
    public function getAllConfigs(): array
    {
        $collection = $this->collectionFactory->create();
        return $collection->getItems();
    }

    /**
     * @param Config[] $configs
     * @throws Exception
     */
    public function updateConfigs(array $configs): void
    {
        foreach ($configs as $config) {
            $config->save();
        }
    }

    /**
     * @param Config[] $configs
     */
    public function removeConfigs(array $configs): void
    {
        $this->configResourceModel->bulkRemove($configs);
    }
}
