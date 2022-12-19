<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Csv\Config;

/**
 * Class Hash
 */
class Hash
{
    /**
     * @param string $value
     * @param string $hash
     * @return bool
     */
    public function verify(string $value, string $hash) : bool
    {
        return $hash === $this->generate($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function generate(?string $value) : ?string
    {
        return sha1($value !== null ? $value : 'null');
    }
}
