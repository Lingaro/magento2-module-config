<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Test\Unit\Model\Csv\Config\Validator;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Lingaro\Config\Model\Csv\Config\Validator\PossibleValuesInColumns;
use PHPUnit\Framework\MockObject\MockObject;

class PossibleValuesInColumnsTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var PossibleValuesInColumns */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(PossibleValuesInColumns::class);
        $this->arguments['columns'] = [
            [
                'name' => 'scope',
                'values' => [
                    'website',
                    'store'
                ]
            ],
            [
                'name' => 'state',
                'values' => [
                    'once',
                    'init',
                    'always'
                ]
            ]
        ];
        $this->validator = $this->objectManager->getObject(PossibleValuesInColumns::class, $this->arguments);
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider dataForValidationProvider
     */
    public function testPossibleValuesInColumnsAreValidWithoutOtherValues(array $data): void
    {
        $this->assertEmpty($this->validator->validate($data));
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider dataForValidationProvider
     */
    public function testValidationDoesntThrowExceptionWhenColumnDoesntExistForSomeRows(array $data): void
    {
        unset($data['scope']);

        $this->assertEmpty($this->validator->validate($data));
    }

    /**
     * @param array $data
     * @throws LocalizedException
     *
     * @dataProvider dataForValidationProvider
     */
    public function testValidationThrowExceptionWhenColumnContainWrongValue(array $data): void
    {
        $data['scope'] .= 's1';

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/Column .* contains not allowed value/');

        $this->validator->validate($data);
    }

    public function dataForValidationProvider(): array
    {
        return [
            [
                [
                    'scope' => 'website',
                    'state' => 'once'
                ]
            ]
        ];
    }
}
