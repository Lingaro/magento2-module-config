<?php

namespace Orba\Config\Model\Config;

use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Helper\ConfigKeyGenerator;
use Orba\Config\Model\StateProcessorPool;
use Orba\Config\Model\Config\OperationsRegistryFactory;
use Exception;

class ConfigAnalyzer
{
    /** @var OperationsRegistryFactory */
    private $operationsRegistryFactory;

    /** @var ConfigKeyGenerator */
    private $configKeyGenerator;

    /** @var StateProcessorPool */
    private $stateProcessorPool;

    /**
     * ConfigAnalyzer constructor.
     * @param OperationsRegistryFactory $operationsRegistryFactory
     * @param ConfigKeyGenerator $configKeyGenerator
     * @param StateProcessorPool $stateProcessorPool
     */
    public function __construct(
        OperationsRegistryFactory $operationsRegistryFactory,
        ConfigKeyGenerator $configKeyGenerator,
        StateProcessorPool $stateProcessorPool
    ) {
        $this->operationsRegistryFactory = $operationsRegistryFactory;
        $this->configKeyGenerator = $configKeyGenerator;
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
    ): OperationsRegistry
    {
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
