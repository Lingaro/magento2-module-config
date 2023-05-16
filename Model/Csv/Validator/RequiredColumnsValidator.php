<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Validator;

use Magento\Framework\Exception\LocalizedException;

class RequiredColumnsValidator
{
    /** @var string[] */
    private array $columns;

    /**
     * RequiredColumns constructor.
     * @param string[] $columns
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
        if (count($data) > 0) {
            $headers = $data[0];
            foreach ($this->columns as $column) {
                if (!in_array($column, $headers)) {
                    throw new LocalizedException(
                        __('Required column %1 is not available in the config file', $column)
                    );
                }
            }
        }
    }
}
