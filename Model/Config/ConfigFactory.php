<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Config;

use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory;
use Magento\Store\Model\ScopeInterface;
use Orba\Config\Api\ConfigInterface;

class ConfigFactory
{
    /** @var Factory */
    private Factory $originalConfigFactory;

    /**
     * ConfigFactory constructor.
     * @param Factory $originalConfigFactory
     */
    public function __construct(Factory $originalConfigFactory)
    {
        $this->originalConfigFactory = $originalConfigFactory;
    }

    /**
     * @param ConfigInterface $csvConfig
     * @return Config
     */
    public function create(ConfigInterface $csvConfig): Config
    {
        $config = $this->originalConfigFactory->create();
        $config->setDataByPath($csvConfig->getPath(), $csvConfig->getValue());
        switch ($csvConfig->getScopeType()) {
            case ScopeInterface::SCOPE_STORES:
                $config->setStore($csvConfig->getScopeCode());
                break;
            case ScopeInterface::SCOPE_WEBSITES:
                $config->setWebsite($csvConfig->getScopeCode());
                break;
            default:
                $config->setScopeId(0);
        }

        return $config;
    }
}
