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
use Lingaro\Config\Model\Config\ConfigFactory;

/**
 * Class Once
 * Update value only if it is the first time it is installed using this command
 */
class Once implements StateProcessorInterface
{
    /** @var ConfigFactory */
    protected ConfigFactory $configFactory;

    /**
     * Once constructor.
     * @param ConfigFactory $configFactory
     */
    public function __construct(ConfigFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

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
        $databaseHash = $databaseConfig->getimportedValueHash();
        if ($databaseHash === $fileConfig->getimportedValueHash()) {
            $operationsRegistry->addIgnored($fileConfig);
            return;
        }

        $databaseValue = $databaseConfig->getValue();
        if ($databaseHash && $databaseValue === $fileConfig->getValue()) {
            $operationsRegistry->addIgnored($fileConfig);
            return;
        }

        $operationsRegistry->addToUpdate(
            $fileConfig,
            $databaseConfig
        );
    }
}
