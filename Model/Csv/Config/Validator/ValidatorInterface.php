<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;

interface ValidatorInterface
{
    /**
     * @param array $data Data from whole file
     * @throws LocalizedException
     */
    public function validate(array $data): void;
}
