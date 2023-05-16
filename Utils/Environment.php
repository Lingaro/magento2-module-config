<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Utils;

/**
 * Class Environment
 * @package Lingaro\Config\Utils
 * @codeCoverageIgnore
 */
class Environment
{
    /**
     * @param string $name
     * @param bool $localOnly
     * @return array|false|string
     */
    public function getVariable(string $name, bool $localOnly = false)
    {
        return getenv($name, $localOnly);
    }
}
