<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Config;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Model\Config;
use Orba\Config\Model\ResourceModel\Config\CollectionFactory;
use Orba\Config\Model\ResourceModel\Config as ConfigResourceModel;

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
            if (!($config instanceof Config)) {
                throw new LocalizedException(
                    __('Wrong config object was provided to repository for update')
                );
            }
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
