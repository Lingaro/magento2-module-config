<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Helper;

use Lingaro\Config\Api\ConfigInterface;

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
