<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

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
