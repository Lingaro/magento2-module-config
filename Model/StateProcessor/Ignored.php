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
 * Class Ignored
 * Skip this config
 */
class Ignored implements StateProcessorInterface
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
        $operationsRegistry->addIgnored($fileConfig);
    }
}
