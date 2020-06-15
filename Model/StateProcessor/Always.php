<?php
namespace Orba\Config\Model\StateProcessor;

use Orba\Config\Api\ConfigInterface;
use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Model\Config\OperationsRegistry;
use Orba\Config\Model\StateProcessorInterface;

/**
 * Class Always
 * Update value every time
 */
class Always implements StateProcessorInterface
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
        if (!$databaseConfigs->has($fileConfig)) {
            $operationsRegistry->addToAdd($fileConfig);

            return;
        }

        $databaseConfig = $databaseConfigs->getFromCollection($fileConfig);

        if ($databaseConfig->getValue() !== $fileConfig->getValue()) {
            $operationsRegistry->addToUpdate(
                $fileConfig,
                $databaseConfig
            );

            return;
        }

        if ($databaseConfig->getValue() === $fileConfig->getValue() && !$databaseConfig->getimportedValueHash()) {
            $operationsRegistry->addToUpdateHash(
                $fileConfig,
                $databaseConfig
            );

            return;
        }

        $operationsRegistry->addIgnored($fileConfig);
    }
}
