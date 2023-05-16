<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Config\OperationsRegistry;

use Lingaro\Config\Api\ConfigInterface;

/**
 * Class ConfigChange
 * @package Lingaro\Config\Model\Config\ConfigCollection
 * @codeCoverageIgnore
 */
class ConfigChange
{
    /** @var ConfigInterface */
    private ConfigInterface $oldConfig;

    /** @var ConfigInterface */
    private ConfigInterface $newConfig;

    /**
     * ConfigChange constructor.
     * @param ConfigInterface $oldConfig
     * @param ConfigInterface $newConfig
     */
    public function __construct(ConfigInterface $oldConfig, ConfigInterface $newConfig)
    {
        $this->oldConfig = $oldConfig;
        $this->newConfig = $newConfig;
    }

    /**
     * @return ConfigInterface
     */
    public function getOldConfig(): ConfigInterface
    {
        return $this->oldConfig;
    }

    /**
     * @return ConfigInterface
     */
    public function getNewConfig(): ConfigInterface
    {
        return $this->newConfig;
    }
}
