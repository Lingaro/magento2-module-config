<?php
namespace Orba\Config\Model;

use Orba\Config\Model\Config\OperationsRegistry;
use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Api\ConfigInterface;

/**
 * Interface StateProcessorInterface
 */
interface StateProcessorInterface
{
    /**
     * @param ConfigInterface $fileConfig
     * @param MappedConfigCollectionInterface $databaseConfigs
     * @param OperationsRegistry $operationsRegistry
     */
    public function process(
        ConfigInterface $fileConfig,
        MappedConfigCollectionInterface $databaseConfigs,
        OperationsRegistry $operationsRegistry
    ) : void;
}
