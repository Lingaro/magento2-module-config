<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;

class RequiredFields implements ValidatorInterface
{
    /** @var string[] */
    private array $requiredFields;

    /**
     * RequiredFields constructor.
     * @param string[] $requiredFields
     */
    public function __construct(array $requiredFields)
    {
        $this->requiredFields = $requiredFields;
    }

    /**
     * @param array $data Single row data
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        foreach ($this->requiredFields as $field) {
            if (empty($data[$field])) {
                throw new LocalizedException(
                    __('Column %1 can not be empty in config file', $field)
                );
            }
        }
    }
}
