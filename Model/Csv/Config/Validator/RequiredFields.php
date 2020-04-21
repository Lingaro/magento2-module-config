<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;

class RequiredFields implements ValidatorInterface
{
    /** @var string[] */
    private $requiredFields;

    /**
     * RequiredFields constructor.
     * @param string[] $requiredFields
     */
    public function __construct(array $requiredFields)
    {
        $this->requiredFields = $requiredFields;
    }

    /**
     * @param array $data Data from whole file
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        $dataLength = count($data);
        foreach ($this->requiredFields as $field) {
            $column = array_column($data, $field);
            $nonEmptyColumn = array_filter($column);
            if ($dataLength !== count($column) || $dataLength !== count($nonEmptyColumn)) {
                throw new LocalizedException(
                    __('Column %1 can not be empty in config file', $field)
                );
            }
        }
    }
}
