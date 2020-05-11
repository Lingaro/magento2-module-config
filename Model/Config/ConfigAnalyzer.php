<?php

namespace Orba\Config\Model\Config;

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

    /** @var MappedConfigCollectionInterface */
    private $databaseConfigs;

    /** @var MappedConfigCollectionInterface */
    private $fileConfigs;

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
        ConfigKeyGenerator $configKeyGenerator,
        MappedConfigCollectionInterface $databaseConfigs,
        MappedConfigCollectionInterface $fileConfigs
    ) {
        $this->operationsRegistryFactory = $operationsRegistryFactory;
        $this->configFactory = $configFactory;
        $this->configKeyGenerator = $configKeyGenerator;
        $this->databaseConfigs = $databaseConfigs;
        $this->fileConfigs = $fileConfigs;
    }

    public function prepareConfigCollection(): OperationsRegistry
    {
        /** @var OperationsRegistry $operationsRegistry */
        $operationsRegistry = $this->operationsRegistryFactory->create();
        foreach ($this->fileConfigs as $fileConfig) {
            switch ($fileConfig->getState()) {
                // skip this config
                case Config::STATE_IGNORED:
                    $operationsRegistry->addIgnored($fileConfig);
                    break;
                // saved only when no config already exist in db
                case Config::STATE_INIT:
                    if ($this->databaseConfigs->has($fileConfig)) {
                        $operationsRegistry->addIgnored($fileConfig);
                    } else {
                        $operationsRegistry->addToAdd($fileConfig);
                    }
                    break;
                // update value only once - when is different in db
                case Config::STATE_ONCE:
                    if ($this->databaseConfigs->has($fileConfig)) {
                        $databaseValue = $this->databaseConfigs->getFromCollection($fileConfig)->getValue();
                        if ($databaseValue ===  $fileConfig->getValue()) {
                            $operationsRegistry->addIgnored($fileConfig);
                        } else {
                            $newConfig = $this->configFactory->create(
                                $fileConfig
                            );
                            $operationsRegistry->addToUpdate(
                                $this->databaseConfigs->getFromCollection($fileConfig),
                                $newConfig
                            );
                        }
                    } else {
                        $operationsRegistry->addToAdd($fileConfig);
                    }
                    break;
                // update value everytime
                case Config::STATE_ALWAYS:
                    if ($this->databaseConfigs->has($fileConfig)) {
                        $operationsRegistry->addToUpdate(
                            $fileConfig,
                            $this->databaseConfigs->getFromCollection($fileConfig)
                        );
                    } else {
                        $operationsRegistry->addToAdd($fileConfig);
                    }
                    break;
                // remove config if exists
                case Config::STATE_ABSENT:
                    if ($this->databaseConfigs->has($fileConfig)) {
                        $operationsRegistry->addToRemove(
                            $fileConfig,
                            $this->databaseConfigs->getFromCollection($fileConfig)
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
