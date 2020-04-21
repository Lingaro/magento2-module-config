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
     * @param array $data Data from whole file
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        foreach ($this->columns as $column) {
            $columnValues = array_column($data, $column['name']);

            $nonPermittedValues = array_filter(
                $columnValues,
                function ($value) use ($column) {
                    return !in_array($value, $column['values']);
                }
            );

            if ($nonPermittedValues !== []) {
                throw new LocalizedException(
                    __('Column %1 contains not allowed values %2', $column['name'], implode(', ', $nonPermittedValues))
                );
            }
        }
    }
}
