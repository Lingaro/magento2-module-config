<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Model\Csv\Config;

interface ValidatorInterface
{
    /**
     * @param array $data Data from whole file
     * @throws LocalizedException
     */
    public function validate(array $data): void;
}
