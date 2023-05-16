<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Test\Unit\Model\Csv\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Lingaro\Config\Model\Csv\Config;
use Lingaro\Config\Model\Csv\Validator\RequiredColumnsValidator;
use PHPUnit\Framework\MockObject\MockObject;

class RequiredColumnsValidatorTest extends BaseTestCase
{
    /** @var MockObject[] */
    private array $arguments;

    /** @var RequiredColumnsValidator */
    private RequiredColumnsValidator $validator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(RequiredColumnsValidator::class);
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider correctCsvDataProvider
     */
    public function testValidatorPassesDataWhereAllRequiredColumnsExist(array $data): void
    {
        $this->arguments['columns'] = $data[0];
        $this->validator = $this->objectManager->getObject(
            RequiredColumnsValidator::class,
            $this->arguments
        );

        $this->assertEmpty($this->validator->validate($data));
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider correctCsvDataProvider
     */
    public function testValidatorThrowsExcaptionWhenAllRequiredColumnsDontExist(array $data): void
    {
        $this->arguments['columns'] = array_merge(
            $data[0],
            [Config::FIELD_ENV_VALUE_PREFIX . 'env1']
        );
        $this->validator = $this->objectManager->getObject(
            RequiredColumnsValidator::class,
            $this->arguments
        );

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/Required column .* is not available in the config file/');
        $this->validator->validate($data);
    }

    /**
     * Provide content read from csv file
     *
     * @return array
     */
    public function correctCsvDataProvider(): array
    {
        return [
            [
                [
                    [
                        Config::FIELD_PATH,
                        Config::FIELD_SCOPE,
                        Config::FIELD_VALUE,
                    ],
                    [
                        'path1',
                        'scope1',
                        'value1',
                    ],
                    [
                        'path2',
                        'scope2',
                        'value2',
                    ]
                ]
            ]
        ];
    }
}
