<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Utils;

/**
 * Class Environment
 * @package Orba\Config\Utils
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
