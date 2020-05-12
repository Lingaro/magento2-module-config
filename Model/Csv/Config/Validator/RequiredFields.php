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
     * @param array $data Single row data
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        foreach ($this->requiredFields as $key => $field) {
            if (empty($data[$field])) {
                throw new LocalizedException(
                    __('Column %1 can not be empty in config file', $field)
                );
            }
        }
    }
}
