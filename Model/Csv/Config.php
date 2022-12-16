<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Csv;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Orba\Config\Api\ConfigInterface;

/**
 * Class Config
 * @package Orba\Config\Model\Csv
 * @codeCoverageIgnore
 */
class Config implements ConfigInterface
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
    public const FIELD_SCOPE_ID = 'scope_id';
    public const FIELD_IMPORTED_VALUE_HASH = 'imported_value_hash';

    public const FIELD_ENV_VALUE_PREFIX = 'value:';

    private ?string $path;
    private ?string $value;
    private ?string $scope;
    private ?string $code;
    private ?string $state;
    private ?int $scopeId;
    private ?string $importedValueHash;

    /**
     * Config constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->path = $data[self::FIELD_PATH];
        $this->value = $data[self::FIELD_VALUE] ?? null;
        $this->code = $data[self::FIELD_CODE] ?? null;
        $this->state = $data[self::FIELD_STATE];
        $this->scopeId = $data[self::FIELD_SCOPE_ID] ?? null;
        $this->importedValueHash = $data[self::FIELD_IMPORTED_VALUE_HASH] ?? '';
        $this->scope = empty($data[self::FIELD_SCOPE])
            ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            : $data[self::FIELD_SCOPE];
    }

    /**
     * @return string|null
     */
    public function getConfigId(): ?string
    {
        return null;
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
    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getScopeType(): string
    {
        return $this->scope;
    }

    public function getScopeCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @return int|null
     */
    public function getScopeId(): ?int
    {
        return $this->scopeId;
    }

    /**
     * @return string
     */
    public function getimportedValueHash() : string
    {
        return $this->importedValueHash;
    }

    /**
     * @return array
     */
    public function getAllData() : array
    {
        return [
            'path' => $this->path,
            'scope' => $this->scope,
            'scope_id' => $this->scopeId,
            'value' => $this->value,
            'imported_value_hash' => $this->importedValueHash
        ];
    }
}
