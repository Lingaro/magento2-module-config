<?php

namespace Orba\Config\Model\Config;

use Orba\Config\Api\ConfigInterface;
use Orba\Config\Api\MappedConfigCollectionInterface;
use Orba\Config\Helper\ConfigKeyGenerator;
use Orba\Config\Model\Csv\Config;
use Orba\Config\Model\Config\OperationsRegistry;
use Orba\Config\Model\Config\OperationsRegistryFactory;

class ConfigAnalyzer
{
    /** @var OperationsRegistryFactory */
    private $operationsRegistryFactory;

    /** @var ConfigFactory */
    private $configFactory;

    /** @var ConfigKeyGenerator */
    private $configKeyGenerator;

    /**
     * ConfigProcessor constructor.
     * @param OperationsRegistryFactory $operationsRegistryFactory
     * @param ConfigFactory $configFactory
     * @param ConfigKeyGenerator $configKeyGenerator
     * @param MappedConfigCollectionInterface $databaseConfigs
     * @param MappedConfigCollectionInterface $fileConfigs
     */
    public function __construct(
        OperationsRegistryFactory $operationsRegistryFactory,
        ConfigFactory $configFactory,
        ConfigKeyGenerator $configKeyGenerator
    ) {
        $this->operationsRegistryFactory = $operationsRegistryFactory;
        $this->configFactory = $configFactory;
        $this->configKeyGenerator = $configKeyGenerator;
    }

    public function prepareConfigCollection(
        MappedConfigCollectionInterface $databaseConfigs,
        MappedConfigCollectionInterface $fileConfigs
    ): OperationsRegistry
    {
        /** @var OperationsRegistry $operationsRegistry */
        $operationsRegistry = $this->operationsRegistryFactory->create();
        foreach ($fileConfigs as $fileConfig) {
            switch ($fileConfig->getState()) {
                // skip this config
                case Config::STATE_IGNORED:
                    $operationsRegistry->addIgnored($fileConfig);
                    break;
                // saved only when no config already exist in db
                case Config::STATE_INIT:
                    if ($databaseConfigs->has($fileConfig)) {
                        $operationsRegistry->addIgnored($fileConfig);
                    } else {
                        $operationsRegistry->addToAdd($fileConfig);
                    }
                    break;
                // update value only once - when is different in db
                case Config::STATE_ONCE:
                    if ($databaseConfigs->has($fileConfig)) {
                        $databaseValue = $databaseConfigs->getFromCollection($fileConfig)->getValue();
                        if ($databaseValue ===  $fileConfig->getValue()) {
                            $operationsRegistry->addIgnored($fileConfig);
                        } else {
                            /** @var ConfigInterface $newConfig */
                            $newConfig = $this->configFactory->create(
                                $fileConfig
                            );
                            $operationsRegistry->addToUpdate(
                                $databaseConfigs->getFromCollection($fileConfig),
                                $newConfig
                            );
                        }
                    } else {
                        $operationsRegistry->addToAdd($fileConfig);
                    }
                    break;
                // update value everytime
                case Config::STATE_ALWAYS:
                    if ($databaseConfigs->has($fileConfig)) {
                        $operationsRegistry->addToUpdate(
                            $fileConfig,
                            $databaseConfigs->getFromCollection($fileConfig)
                        );
                    } else {
                        $operationsRegistry->addToAdd($fileConfig);
                    }
                    break;
                // remove config if exists
                case Config::STATE_ABSENT:
                    if ($databaseConfigs->has($fileConfig)) {
                        $operationsRegistry->addToRemove(
                            $fileConfig,
                            $databaseConfigs->getFromCollection($fileConfig)
                        );
                    } else {
                        $operationsRegistry->addIgnored($fileConfig);
                    }
                    break;
            }
        }
        return $operationsRegistry;
    }
}
