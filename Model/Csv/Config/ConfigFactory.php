<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv\Config;

use Magento\Framework\Exception\LocalizedException;
use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Model\Csv\Config;
use Lingaro\Config\Model\Csv\Config\Validator\ValidatorInterface;
use Lingaro\Config\Model\Csv\Config\Value\ValueParser;
use Lingaro\Config\Helper\ScopeMap;

class ConfigFactory
{
    /** @var ValueParser */
    private $valueParser;

    /** @var ValueParser */
    private $valueGetter;

    /** @var ValidatorInterface[] */
    private $configValidators;

    /** @var ScopeMap */
    private $scopeMap;

    /** @var Hash */
    private $hash;

    /**
     * ConfigFactory constructor.
     * @param ValueParser $valueParser
     * @param ValueGetter $valueGetter
     * @param ScopeMap $scopeMap
     * @param Hash $hash
     * @param array $configValidators
     */
    public function __construct(
        ValueParser $valueParser,
        ValueGetter $valueGetter,
        ScopeMap $scopeMap,
        Hash $hash,
        array $configValidators
    ) {
        $this->valueParser = $valueParser;
        $this->valueGetter = $valueGetter;
        $this->scopeMap = $scopeMap;
        $this->hash = $hash;
        $this->configValidators = $configValidators;
    }

    /**
     * @param array $headers
     * @param array $values
     * @param string|null $env
     * @return ConfigInterface
     * @throws LocalizedException
     */
    public function create(array $headers, array $values, ?string $env = null): ConfigInterface
    {
        $data = array_combine($headers, $values);

        foreach ($this->configValidators as $validator) {
            $validator->validate($data);
        }

        $valueColumnName = $env ? (Config::FIELD_ENV_VALUE_PREFIX . $env) : Config::FIELD_VALUE;
        if (!in_array($valueColumnName, $headers)) {
            throw new LocalizedException(__('Value column %1 does not exist', $valueColumnName));
        }
        $data[Config::FIELD_VALUE] = $this->valueParser->parse($data[$valueColumnName]);

        $data[Config::FIELD_VALUE] = $this->valueGetter->getValueWithBackendModel(
            $data[Config::FIELD_PATH],
            $data[Config::FIELD_VALUE]
        );

        $data[Config::FIELD_IMPORTED_VALUE_HASH] = $this->hash->generate($data[Config::FIELD_VALUE]);

        $data[Config::FIELD_SCOPE_ID] = $this->scopeMap->getIdByScopeAndCode(
            $data[Config::FIELD_SCOPE],
            $data[Config::FIELD_CODE] ?? ''
        );

        return new Config($data);
    }
}
