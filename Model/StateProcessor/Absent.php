<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\StateProcessor;

use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Api\MappedConfigCollectionInterface;
use Lingaro\Config\Model\Config\OperationsRegistry;
use Lingaro\Config\Model\StateProcessorInterface;

/**
 * Class Absent
 * Remove config if it exists
 */
class Absent implements StateProcessorInterface
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
            $operationsRegistry->addIgnored($fileConfig);
        } else {
            $operationsRegistry->addToRemove(
                $fileConfig,
                $databaseConfigs->getFromCollection($fileConfig)
            );
        }
    }
}
