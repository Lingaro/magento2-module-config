<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Helper;

use Orba\Config\Api\ConfigInterface;

class ConfigKeyGenerator
{
    /**
     * @param ConfigInterface $config
     * @return string
     */
    public function generateKey(ConfigInterface $config): string
    {
        return implode(
            '|',
            [
                $config->getPath(),
                $config->getScopeType(),
                $config->getScopeId()
            ]
        );
    }
}
