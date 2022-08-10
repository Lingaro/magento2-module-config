<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Config;

use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Model\StateProcessorPool;
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
