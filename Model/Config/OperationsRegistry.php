<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Config;

use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Model\Config\OperationsRegistry\ConfigChange;

class OperationsRegistry
{
    /** @var ConfigInterface[] */
    private array $toAddConfigs;

    /** @var ConfigChange[] */
    private array $toUpdateConfigs;

    /** @var ConfigChange[] */
    private array $toUpdateHashConfigs;

    /** @var ConfigChange[] */
    private array $toRemoveConfigs;

    /** @var ConfigInterface[] */
    private array $ignoredConfigs;

    /**
     * ConfigCollection constructor.
     */
    public function __construct()
    {
        $this->toAddConfigs = [];
        $this->toUpdateConfigs = [];
        $this->toUpdateHashConfigs = [];
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

        return $this;
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

        return $this;
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
    public function addToUpdateHash(ConfigInterface $config, ConfigInterface $oldConfig): OperationsRegistry
    {
        $this->toUpdateConfigs[] = new ConfigChange($oldConfig, $config);

        return $this;
    }

    /**
     * @return ConfigChange[]
     */
    public function getToUpdateHashConfigs(): array
    {
        return $this->toUpdateHashConfigs;
    }

    /**
     * @param ConfigInterface $config
     * @param ConfigInterface $oldConfig
     * @return OperationsRegistry
     */
    public function addToRemove(ConfigInterface $config, ConfigInterface $oldConfig): OperationsRegistry
    {
        $this->toRemoveConfigs[] = new ConfigChange($oldConfig, $config);

        return $this;
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
