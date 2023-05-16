<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Csv;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Lingaro\Config\Model\Csv\Config\ConfigFactory;
use Lingaro\Config\Model\Csv\Validator\RequiredColumnsValidator;
use Lingaro\Config\Model\MappedConfigCollection;
use Lingaro\Config\Model\MappedConfigCollectionFactory;

class Reader
{
    /** @var Csv */
    private Csv $csv;

    /** @var ConfigFactory */
    private ConfigFactory $configFactory;

    /** @var RequiredColumnsValidator */
    private RequiredColumnsValidator $requiredColumnsValidator;

    /** @var MappedConfigCollection */
    private MappedConfigCollection $mappedConfigCollection;

    /**
     * Reader constructor.
     * @param Csv $csv
     * @param ConfigFactory $configFactory
     * @param RequiredColumnsValidator $requiredColumnsValidator
     * @param MappedConfigCollectionFactory $mappedConfigCollectionFactory
     */
    public function __construct(
        Csv $csv,
        ConfigFactory $configFactory,
        RequiredColumnsValidator $requiredColumnsValidator,
        MappedConfigCollectionFactory $mappedConfigCollectionFactory
    ) {
        $this->csv = $csv;
        $this->configFactory = $configFactory;
        $this->requiredColumnsValidator = $requiredColumnsValidator;
        $this->mappedConfigCollection = $mappedConfigCollectionFactory->create();
    }

    /**
     * @param string $path
     * @param string|null $env
     * @return MappedConfigCollection
     * @throws LocalizedException
     */
    public function readConfigFile(string $path, ?string $env = null): MappedConfigCollection
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

        foreach ($data as $row) {
            $config = $this->configFactory->create($headers, $row, $env);
            $this->mappedConfigCollection->add($config);
        }

        return $this->mappedConfigCollection;
    }
}
