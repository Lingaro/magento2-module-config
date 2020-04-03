<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    public const STATE_ALWAYS = 'always';
    public const STATE_INIT = 'init';
    public const STATE_IGNORED = 'ignored';
    public const STATE_ONCE = 'once';
    public const STATE_ABSENT = 'absent';

    public const FIELD_PATH = 'path';
    public const FIELD_VALUE = 'value';
    public const FIELD_SCOPE = 'scope';
    public const FIELD_CODE = 'code';
    public const FIELD_STATE = 'state';

    public const FIELD_ENV_VALUE_PREFIX = 'value:';

    private $path;
    private $value;
    private $scope;
    private $code;
    private $state;

    /**
     * Config constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->path = $data[self::FIELD_PATH];
        $this->value = $data[self::FIELD_VALUE];
        $this->scope = empty($data[self::FIELD_SCOPE]) ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : $data[self::FIELD_SCOPE];
        $this->code = $data[self::FIELD_CODE];
        $this->state = $data[self::FIELD_STATE];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }
}
