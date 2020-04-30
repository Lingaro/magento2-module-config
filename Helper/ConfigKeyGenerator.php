<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Helper;

use Orba\Config\Model\Csv\Config as CsvConfig;
use Orba\Config\Model\Config as DbConfig;

class ConfigKeyGenerator
{
    /**
     * @param CsvConfig $config
     * @return string
     */
    public function generateForCsv(CsvConfig $config): string
    {
        return $config->getPath() . $config->getScope() . $config->getCode();
    }

    /**
     * @param DbConfig $config
     * @return string
     */
    public function generateForDb(DbConfig $config): string
    {
        return $config->getPath() . $config->getScope() . $config->getScopeCode();
    }
}
