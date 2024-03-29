<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;

class PossibleValuesInColumns implements ValidatorInterface
{
    /** @var array */
    private array $columns;

    /**
     * PossibleValuesInColumns constructor.
     * @param array columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param array $data Single row data
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        foreach ($this->columns as $column) {
            if (isset($data[$column['name']])) {
                $value = $data[$column['name']];
                if (!in_array($value, $column['values'])) {
                    throw new LocalizedException(
                        __('Column %1 contains not allowed value %2', $column['name'], $value)
                    );
                }
            }
        }
    }
}
