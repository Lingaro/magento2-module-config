<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Config;

use Lingaro\Config\Api\MappedConfigCollectionInterface;
use Lingaro\Config\Model\StateProcessorPool;
use Exception;

class ConfigAnalyzer
{
    /** @var OperationsRegistryFactory */
    private OperationsRegistryFactory $operationsRegistryFactory;

    /** @var StateProcessorPool */
    private StateProcessorPool $stateProcessorPool;

    /**
     * ConfigAnalyzer constructor.
     * @param OperationsRegistryFactory $operationsRegistryFactory
     * @param StateProcessorPool $stateProcessorPool
     */
    public function __construct(
        OperationsRegistryFactory $operationsRegistryFactory,
        StateProcessorPool $stateProcessorPool
    ) {
        $this->operationsRegistryFactory = $operationsRegistryFactory;
        $this->stateProcessorPool = $stateProcessorPool;
    }

    /**
     * @param MappedConfigCollectionInterface $databaseConfigs
     * @param MappedConfigCollectionInterface $fileConfigs
     * @return OperationsRegistry
     * @throws Exception
     */
    public function prepareConfigCollection(
        MappedConfigCollectionInterface $databaseConfigs,
        MappedConfigCollectionInterface $fileConfigs
    ): OperationsRegistry {
        $operationsRegistry = $this->operationsRegistryFactory->create();
        foreach ($fileConfigs as $fileConfig) {
            $this->stateProcessorPool
                ->get($fileConfig->getState())
                ->process(
                    $fileConfig,
                    $databaseConfigs,
                    $operationsRegistry
                );
        }

        return $operationsRegistry;
    }
}
