<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;

interface ValidatorInterface
{
    /**
     * @param array $data Data from whole file
     * @throws LocalizedException
     */
    public function validate(array $data): void;
}
