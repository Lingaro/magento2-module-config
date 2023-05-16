<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Config;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Api\MappedConfigCollectionInterface;
use Lingaro\Config\Model\ResourceModel\Config\CollectionFactory;
use Lingaro\Config\Model\ResourceModel\Config as ConfigResourceModel;
use Lingaro\Config\Model\MappedConfigCollectionFactory;
use Lingaro\Config\Helper\ScopeMap;

/**
 * Class ConfigRepository
 * @package Lingaro\Config\Model\Config
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

    /** @var ScopeMap */
    private $scopeMap;

    /**
     * ConfigRepository constructor.
     * @param CollectionFactory $collectionFactory
     * @param ConfigResourceModel $configResourceModel
     * @param MappedConfigCollectionFactory $mappedConfigCollectionFactory
     * @param ScopeMap $scopeMap
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ConfigResourceModel $configResourceModel,
        MappedConfigCollectionFactory $mappedConfigCollectionFactory,
        ScopeMap $scopeMap
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configResourceModel = $configResourceModel;
        $this->mappedConfigCollectionFactory = $mappedConfigCollectionFactory;
        $this->scopeMap = $scopeMap;
    }

    /**
     * @param string $path
     * @param string $scope
     * @param mixed|null $scopeIdOrCode
     * @return ConfigInterface
     * @throws NoSuchEntityException
     */
    public function get(
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeIdOrCode = null
    ): ConfigInterface {
        $collection = $this->collectionFactory->create();
        $collection->addFilter('path', $path);
        $collection->addFilter('scope', $scope);

        $scopeId = null;
        if ($scope !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $scopeId = is_numeric($scopeIdOrCode)
                ? $scopeIdOrCode
                : $this->scopeMap->getIdByScopeAndCode($scope, $scopeIdOrCode);
            $collection->addFilter('scope_id', $scopeId);
        }

        $config = $collection->getFirstItem();

        if (!$config instanceof ConfigInterface) {
            throw new NoSuchEntityException(__(sprintf(
                'No config found for path %s, scope %s and scope_id %s',
                $path,
                $scope,
                $scopeId
            )));
        }

        return $config;
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
    public function insertConfigs(array $configs): void
    {
        $this->configResourceModel->bulkInsert($configs);
    }

    /**
     * @param ConfigInterface[] $configs
     * @throws Exception
     */
    public function updateConfigs(array $configs): void
    {
        $this->configResourceModel->bulkUpdate($configs);
    }

    /**
     * @param ConfigInterface[] $configs
     */
    public function removeConfigs(array $configs): void
    {
        $this->configResourceModel->bulkRemove($configs);
    }
}
