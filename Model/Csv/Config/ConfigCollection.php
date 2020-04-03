<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv\Config;

class ConfigCollection
{
    /** @var Config[] */
    private $all;

    /** @var Config[] */
    private $active;

    /**
     * ConfigCollection constructor.
     * @param Config[] $configs
     */
    public function __construct(array $configs)
    {
        $this->all = $configs;
    }

    /**
     * @return Config[]
     */
    public function getAll(): array
    {
        return $this->all;
    }

    /**
     * @return Config[]
     */
    public function getActive(): array
    {
        if ($this->active === null) {
            foreach ($this->all as $key => $config) {
                if ($config->getState() !== Config::STATE_IGNORED) {
                    $this->active[$key] = $config;
                }
            }
        }
        return $this->active;
    }
}
