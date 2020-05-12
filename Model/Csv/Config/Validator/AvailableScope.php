<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv\Config\Validator;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Helper\ScopeMap;
use Orba\Config\Model\Csv\Config;

/**
 * Class AvailableScope
 * @package Orba\Config\Model\Csv\Config\Validator
 */
class AvailableScope implements ValidatorInterface
{
    /** @var ScopeMap */
    private $scopeMap;

    public function __construct(
        ScopeMap $scopeMap
    ) {
        $this->scopeMap = $scopeMap;
    }

    /**
     * @param array $data Single row data
     * @throws LocalizedException
     */
    public function validate(array $data): void
    {
        if (empty($data[Config::FIELD_SCOPE]) || $data[Config::FIELD_SCOPE] === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            return;
        }

        if ($this->scopeMap->getIdByScopeAndCode($data[Config::FIELD_SCOPE], $data[Config::FIELD_CODE] ?? '') === null) {
            throw new LocalizedException(
                __('No such entity with code %1 in scope %2', $data[Config::FIELD_CODE], $data[Config::FIELD_SCOPE])
            );
        }
    }
}
