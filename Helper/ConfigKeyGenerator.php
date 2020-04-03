<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Helper;

use Orba\Config\Model\Csv\Config;

class ConfigKeyGenerator
{
    /**
     * @param Config $config
     * @return string
     */
    public function generateForCsv(Config $config): string
    {
        return $config->getPath() . $config->getScope() . $config->getCode();
    }
}
