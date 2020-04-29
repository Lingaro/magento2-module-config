<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv\Config;

use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Model\Csv\Config;
use Orba\Config\Model\Csv\Config\Validator\ValidatorInterface;
use Orba\Config\Model\Csv\Config\Value\ValueParser;

class ConfigFactory
{
    /** @var ValueParser */
    private $valueParser;

    /** @var ValueParser */
    private $valueGetter;

    /** @var ValidatorInterface[] */
    private $configValidators;

    /**
     * ConfigFactory constructor.
     * @param ValueParser $valueParser
     * @param ValueGetter $valueGetter
     * @param ValidatorInterface[] $configValidators
     */
    public function __construct(ValueParser $valueParser, ValueGetter $valueGetter, array $configValidators)
    {
        $this->valueParser = $valueParser;
        $this->valueGetter = $valueGetter;
        $this->configValidators = $configValidators;
    }

    /**
     * @param array $headers
     * @param string[] $values
     * @param string|null $env
     * @return Config
     * @throws LocalizedException
     */
    public function create(array $headers, array $values, ?string $env = null): Config
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

        return new Config($data);
    }
}
