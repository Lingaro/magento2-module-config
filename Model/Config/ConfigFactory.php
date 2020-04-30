<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Config;

use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Orba\Config\Model\Csv\Config as CsvConfig;

class ConfigFactory
{
    /** @var Factory */
    private $originalConfigFactory;

    /**
     * ConfigFactory constructor.
     * @param Factory $originalConfigFactory
     */
    public function __construct(Factory $originalConfigFactory)
    {
        $this->originalConfigFactory = $originalConfigFactory;
    }

    /**
     * @param CsvConfig $csvConfig
     * @return Config
     */
    public function create(CsvConfig $csvConfig): Config
    {
        $config = $this->originalConfigFactory->create();
        $config->setDataByPath($csvConfig->getPath(), $csvConfig->getValue());
        switch ($csvConfig->getScope()) {
            case ScopeInterface::SCOPE_STORES:
                $config->setStore($csvConfig->getCode());
                break;
            case ScopeInterface::SCOPE_WEBSITES:
                $config->setWebsite($csvConfig->getCode());
                break;
        }
        return $config;
    }
}
