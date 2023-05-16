<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config;

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
