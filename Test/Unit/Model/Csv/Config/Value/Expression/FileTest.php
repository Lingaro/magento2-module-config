<?php

namespace Orba\Config\Test\Unit\Model\Csv\Config\Value\Expression;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\BaseTestCase;
use Orba\Config\Model\Csv\Config\Value\Expression\File;
use PHPUnit\Framework\MockObject\MockObject;

class FileTest extends BaseTestCase
{
    /** @var MockObject[] */
    private $arguments;

    /** @var File */
    private $file;

    protected function setUp()
    {
        parent::setUp();
        $this->arguments = $this->objectManager->getConstructArguments(File::class);
        $this->file = $this->objectManager->getObject(File::class, $this->arguments);
    }

    public function testNameIsCorrect(): void
    {
        $this->assertEquals('file', $this->file->getName());
    }

    public function testRealValueCannotBeReadForNonReadableFile(): void
    {
        $name = '/var/www/magento.txt';
        $this->arguments['driverFile']->expects($this->once())
        ->method('isReadable')
        ->with($name)
        ->willReturn(false);

        $this->arguments['driverFile']->expects($this->never())
            ->method('fileGetContents')
            ->with($name);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageRegExp('/File .* can not be read/');

        $this->file->getRealValue($name);
    }

    public function testRealValueIsReadForReadableFile(): void
    {
        $name = '/var/www/magento.txt';
        $value = 'secret content';
        $this->arguments['driverFile']->expects($this->once())
            ->method('isReadable')
            ->with($name)
            ->willReturn(true);

        $this->arguments['driverFile']->expects($this->once())
            ->method('fileGetContents')
            ->with($name)
            ->willReturn($value);

        $this->assertEquals($value, $this->file->getRealValue($name));
    }

    /**
     * @param string $rawValue
     * @param array $expectedValue
     *
     * @dataProvider envVariablesDataProvider
     */
    public function testExpressionIsMatchedCorrectly(
        string $rawValue,
        array $expectedValue
    ): void {
        $matched = $this->file->match($rawValue);
        $this->assertEquals($expectedValue, $matched);
    }

    /**
     * Provide data for file matching operation
     *
     * @return array
     */
    public function envVariablesDataProvider(): array
    {
        return [
            'only one existing expression' => [
                '{{file /var/www/magento.txt}}',
                ['{{file /var/www/magento.txt}}' => '/var/www/magento.txt']
            ],
            'one existing expression with additional chars' => [
                'prefix{{file /var/www/magento.txt}}suffix',
                ['{{file /var/www/magento.txt}}' => '/var/www/magento.txt']
            ],
            'one expression existing two times with additional chars' => [
                'prefix{{file /var/www/magento.txt}}suffix{{file /var/www/magento.txt}}suffix2',
                ['{{file /var/www/magento.txt}}' => '/var/www/magento.txt']
            ],
            'two different existing expression with additional chars' => [
                'prefix{{file /var/www/magento1.txt}}suffix{{file /var/www/magento2.txt}}suffix2',
                ['{{file /var/www/magento1.txt}}' => '/var/www/magento1.txt', '{{file /var/www/magento2.txt}}' => '/var/www/magento2.txt']
            ]
        ];
    }
}
