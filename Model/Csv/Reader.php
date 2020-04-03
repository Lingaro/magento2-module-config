<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\Csv;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Orba\Config\Helper\ConfigKeyGenerator;
use Orba\Config\Model\Csv\Config\ConfigFactory;
use Orba\Config\Model\Csv\Validator\RequiredColumnsValidator;

class Reader
{
    /** @var Csv */
    private $csv;

    /** @var ConfigFactory */
    private $configFactory;

    /** @var ConfigKeyGenerator */
    private $configKeyGenerator;

    /** @var RequiredColumnsValidator */
    private $requiredColumnsValidator;

    /**
     * Reader constructor.
     * @param Csv $csv
     * @param ConfigFactory $configFactory
     * @param ConfigKeyGenerator $configKeyGenerator
     * @param RequiredColumnsValidator $requiredColumnsValidator
     */
    public function __construct(
        Csv $csv, ConfigFactory $configFactory,
        ConfigKeyGenerator $configKeyGenerator,
        RequiredColumnsValidator $requiredColumnsValidator
    ) {
        $this->csv = $csv;
        $this->configFactory = $configFactory;
        $this->configKeyGenerator = $configKeyGenerator;
        $this->requiredColumnsValidator = $requiredColumnsValidator;
    }

    /**
     * @param string $path
     * @param string|null $env
     * @return Config[]
     * @throws LocalizedException
     */
    public function readConfigFile(string $path, ?string $env = null): array
    {
        try {
            $data = $this->csv->getData($path);
        } catch (Exception $e) {
            throw new LocalizedException(
                __('File %1 can not be read', $path)
            );
        }
        $this->requiredColumnsValidator->validate($data);

        // remove headers from data
        $headers = $data[0];
        $data = array_slice($data, 1);

        $configs = [];
        foreach ($data as $row) {
            $config = $this->configFactory->create($headers, $row, $env);
            $key = $this->configKeyGenerator->generateForCsv($config);
            $configs[$key] = $config;
        }
        return $configs;
    }
}
