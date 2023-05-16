<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Test\Unit\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Lingaro\Config\Model\Csv\Config\Validator\RequiredFields;
use PHPUnit\Framework\MockObject\MockObject;

class RequiredFieldsTest extends BaseTestCase
{
    const REQUIRED_FIELDS = [
        'path',
        'value',
        'scope'
    ];
    /** @var MockObject[] */
    private $arguments;

    /** @var RequiredFields */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(RequiredFields::class);
        $this->arguments['requiredFields'] = self::REQUIRED_FIELDS;
        $this->validator = $this->objectManager->getObject(RequiredFields::class, $this->arguments);
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider dataForValidationProvider
     */
    public function testValidationDoesntThrowExceptionWhenAllRequiredFieldsAreNotEmpty(array $data): void
    {
        $this->assertEmpty($this->validator->validate($data));
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider dataForValidationProvider
     */
    public function testValidationThrowExceptionWhenRequiredColumnDoesntExist(array $data): void
    {
        unset($data[self::REQUIRED_FIELDS[0]]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/Column .* can not be empty in config file/');

        $this->validator->validate($data);
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider dataForValidationProvider
     */
    public function testValidationThrowExceptionWhenRequiredColumnIsEmpty(array $data): void
    {
        $data[self::REQUIRED_FIELDS[0]] = '';

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/Column .* can not be empty in config file/');

        $this->validator->validate($data);
    }

    public function dataForValidationProvider(): array
    {
        return [
            [
                [
                    'path' => '/a/b/c',
                    'code' => '',
                    'value' => '43',
                    'scope' => 'website',
                    'state' => 'once'
                ]
            ]
        ];
    }
}
