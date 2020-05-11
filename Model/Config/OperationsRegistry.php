<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Config;

use Orba\Config\Api\ConfigInterface;
use Orba\Config\Model\Config\OperationsRegistry\ConfigChange;

class OperationsRegistry
{
    /** @var ConfigInterface[] */
    private $toAddConfigs;

    /** @var ConfigChange[] */
    private $toUpdateConfigs;

    /** @var ConfigChange[] */
    private $toRemoveConfigs;

    /** @var ConfigInterface[] */
    private $ignoredConfigs;

    /**
     * ConfigCollection constructor.
     */
    public function __construct()
    {
        $this->toAddConfigs = [];
        $this->toUpdateConfigs = [];
        $this->toRemoveConfigs = [];
        $this->ignoredConfigs = [];
    }

    /**
     * @param ConfigInterface $config
     * @return OperationsRegistry
     */
    public function addToAdd(ConfigInterface $config): OperationsRegistry
    {
        $this->toAddConfigs[] = $config;
    }

    /**
     * @return ConfigInterface[]
     */
    public function getToAddConfigs(): array
    {
        return $this->toAddConfigs;
    }

    /**
     * @param ConfigInterface $config
     * @param ConfigInterface $oldConfig
     * @return OperationsRegistry
     */
    public function addToUpdate(ConfigInterface $config, ConfigInterface $oldConfig): OperationsRegistry
    {
        $this->toUpdateConfigs[] = new ConfigChange($oldConfig, $config);
    }

    /**
     * @return ConfigChange[]
     */
    public function getToUpdateConfigs(): array
    {
        return $this->toUpdateConfigs;
    }

    /**
     * @param ConfigInterface $config
     * @param ConfigInterface $oldConfig
     * @return OperationsRegistry
     */
    public function addToRemove(ConfigInterface $config, ConfigInterface $oldConfig): OperationsRegistry
    {
        $this->toRemoveConfigs[] = new ConfigChange($oldConfig, $config);
    }

    /**
     * @return ConfigChange[]
     */
    public function getToRemoveConfigs(): array
    {
        return $this->toRemoveConfigs;
    }

    /**
     * @param ConfigInterface $config
     * @return OperationsRegistry
     */
    public function addIgnored(ConfigInterface $config): OperationsRegistry
    {
        $this->ignoredConfigs[] = $config;
        return $this;
    }

    /**
     * @return ConfigInterface[]
     */
    public function getIgnoredConfigs(): array
    {
        return $this->ignoredConfigs;
    }
}
