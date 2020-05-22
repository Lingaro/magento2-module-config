<?php
namespace Orba\Config\Model\StateProcessor;

use Orba\Config\Api\ConfigInterface;
use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Model\Config\OperationsRegistry;
use Orba\Config\Model\StateProcessorInterface;

/**
 * Class Init
 * Save only when no config already exist in db
 */
class Init implements StateProcessorInterface
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
    ): void {
        if ($databaseConfigs->has($fileConfig)) {
            $operationsRegistry->addIgnored($fileConfig);
        } else {
            $operationsRegistry->addToAdd($fileConfig);
        }
    }
}
