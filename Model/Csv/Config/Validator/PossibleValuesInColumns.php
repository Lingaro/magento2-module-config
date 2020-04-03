<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;

class PossibleValuesInColumns implements ValidatorInterface
{
    /** @var array */
    private $columns;

    /**
     * PossibleValuesInColumns constructor.
     * @param array columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param array $data
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        foreach ($this->columns as $column) {
            if (!in_array($column['values'], $data[$column['name']])) {
                throw new LocalizedException(
                    __('Column %1 contains not allowed value %2', $column['name'], $data[$column['name']])
                );
            }
        }
    }
}
