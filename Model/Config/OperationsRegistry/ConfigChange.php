<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Config\OperationsRegistry;

use Orba\Config\Api\ConfigInterface;

/**
 * Class ConfigChange
 * @package Orba\Config\Model\Config\ConfigCollection
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
