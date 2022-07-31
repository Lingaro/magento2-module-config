<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Test\Unit\Model\Csv\Config;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Csv\Config\ValueGetter;
use PHPUnit\Framework\MockObject\MockObject;

class ValueGetterTest extends BaseTestCase
{
    /** @var MockObject[] */
    private array $arguments;

    /** @var ValueGetter */
    private ValueGetter $valueGetter;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(ValueGetter::class);
        $this->valueGetter = $this->objectManager->getObject(ValueGetter::class, $this->arguments);
    }

    /**
     * @return void
     */
    public function testOriginalValueIsReturnedWhenFieldDoesntHaveBackendModel(): void
    {
        $path = 'path/to/config';
        $value = 'value';

        $fieldMock = $this->basicMock(Field::class);
        $fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(false);

        $structureMock = $this->basicMock(Structure::class);
        $structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($path)
            ->willReturn($fieldMock);
        $this->arguments['structureFactory']->expects($this->once())
            ->method('create')
            ->willReturn($structureMock);

        $this->arguments['valueFactory']->expects($this->never())
            ->method('create');

        $this->assertEquals($value, $this->valueGetter->getValueWithBackendModel($path, $value));
    }

    /**
     * @return void
     */
    public function testOriginalValueIsReturnedWhenBackendModelDoesntSupportParsing(): void
    {
        $path = 'path/to/config';
        $value = 'value';

        $backendModelClass = 'Backend/Class/Model';
        $fieldMock = $this->basicMock(Field::class);
        $fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $fieldMock->expects($this->once())
            ->method('getData')
            ->willReturn(
                [
                    'backend_model' => $backendModelClass
                ]
            );

        $structureMock = $this->basicMock(Structure::class);
        $structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($path)
            ->willReturn($fieldMock);
        $this->arguments['structureFactory']->expects($this->once())
            ->method('create')
            ->willReturn($structureMock);

        $this->arguments['valueFactory']->expects($this->once())
            ->method('create')
            ->with($backendModelClass, [])
            ->willReturn(
                $this->basicMock(ValueInterface::class)
            );

        $this->assertEquals($value, $this->valueGetter->getValueWithBackendModel($path, $value));
    }

    /**
     * @return void
     */
    public function testParsedValueIsReturnedWhenBackendModelCanParseIt(): void
    {
        $path = 'path/to/config';
        $value = 'value';
        $parsedValue = 'parsedValue';

        $backendModelClass = 'Backend/Class/Model';
        $fieldMock = $this->basicMock(Field::class);
        $fieldMock->expects($this->once())
            ->method('hasBackendModel')
            ->willReturn(true);
        $fieldMock->expects($this->once())
            ->method('getData')
            ->willReturn(
                [
                    'backend_model' => $backendModelClass
                ]
            );

        $structureMock = $this->basicMock(Structure::class);
        $structureMock->expects($this->once())
            ->method('getElementByConfigPath')
            ->with($path)
            ->willReturn($fieldMock);
        $this->arguments['structureFactory']->expects($this->once())
            ->method('create')
            ->willReturn($structureMock);

        $backendModelMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setValue',
                    ValueGetter::MODEL_PARSE_METHOD,
                    'getValue'
                ]
            )->getMockForAbstractClass();
        $backendModelMock->expects($this->once())
            ->method('setValue')
            ->with($value);
        $backendModelMock->expects($this->once())
            ->method(ValueGetter::MODEL_PARSE_METHOD);
        $backendModelMock->expects($this->once())
            ->method('getValue')
            ->willReturn($parsedValue);

        $this->arguments['valueFactory']->expects($this->once())
            ->method('create')
            ->with($backendModelClass, [])
            ->willReturn($backendModelMock);

        $this->assertEquals($parsedValue, $this->valueGetter->getValueWithBackendModel($path, $value));
    }
}
