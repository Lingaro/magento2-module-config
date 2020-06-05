<?php

namespace Orba\Config\Test\Integration\Console\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Orba\Config\Console\Command\ConfigCommand;
use Orba\Config\Model\Csv\Config;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ConfigCommandTest
 */
class ConfigCommandTest extends TestCase
{
    const CSV_HEADERS = [
        0 => Config::FIELD_PATH,
        1 => Config::FIELD_SCOPE,
        2 => Config::FIELD_CODE,
        3 => Config::FIELD_VALUE,
        4 => Config::FIELD_STATE
    ];

    const DEFAULT_CONFIG_PATH = 'test/path';

    const DEFAULT_CSV_FILE_NAME = 'test.csv';


    /** @var ObjectManager */
    private $objectManager;

    /** @var ConfigCommand */
    private $command;
    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var Filesystem
     */
    protected $fileSystem;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var WriterInterface
     */
    private $configWriter;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fileSystem = $this->objectManager->get(Filesystem::class);
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->command = $this->objectManager->get(ConfigCommand::class);
        $this->configWriter = $this->objectManager->get(WriterInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->tester = new CommandTester(
            $this->command
        );

    }

    public function testCommandReturnsErrorWhenNoFileSpecified()
    {
        $this->tester->execute([]);
        $this->assertContains('Please specify at least one file with configuration', $this->tester->getDisplay());
    }

    public function testCommandReturnsErrorWhenParamFilesIsNotArray()
    {
        $this->tester->execute(['files' => 'test.csv']);
        $this->assertContains('Parameter must be an array', $this->tester->getDisplay());
        $this->assertEquals(Cli::RETURN_FAILURE, $this->tester->getStatusCode());
    }

    public function testCommandReturnsErrorWhenFileCanNotBeRead()
    {
        $this->tester->execute(['files' => ['test.csv']]);
        $this->assertContains('File test.csv can not be read', $this->tester->getDisplay());
        $this->assertEquals(Cli::RETURN_FAILURE, $this->tester->getStatusCode());
    }

    /**
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccess()
    {
        $value = 'test_1';
        $store = $this->getStore();
        $csvData = $this->getDefaultCsvData(Config::STATE_ALWAYS, $value, self::DEFAULT_CONFIG_PATH, $store->getCode());


        $this->tester->execute(['files' => [$this->newCsvFile(self::DEFAULT_CSV_FILE_NAME, $csvData)]]);
        $this->assertEquals(
            $this->scopeConfig->getValue(
                self::DEFAULT_CONFIG_PATH,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $store->getCode()
            ),
            $value
        );
        $this->assertContains('Configuration has been updated successfully', $this->tester->getDisplay());
        $this->assertEquals(Cli::RETURN_SUCCESS, $this->tester->getStatusCode());
    }

    /**
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccessSetSateAbsentAfterSetStateAlways()
    {
        $csvFileName = 'test.csv';
        $configPath = 'test/absent/after/always';
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $value = '2';
        $csvData = [
            'header' => self::CSV_HEADERS,
            'row' => [
                'config_path' => $configPath,
                'scope' => $scope,
                'code' => '',
                'value' => $value,
                'state' => Config::STATE_ALWAYS
            ]
        ];

        $this->tester->execute(['files' => [$this->newCsvFile($csvFileName, $csvData)]]);
        $this->assertEquals($value, $this->scopeConfig->getValue($configPath, $scope));

        $csvData['row']['state'] = Config::STATE_ABSENT;

        $this->tester->execute(['files' => [$this->newCsvFile($csvFileName, $csvData)]]);
        $this->assertEquals(null, $this->scopeConfig->getValue($configPath, $scope));
        $this->assertEquals(Cli::RETURN_SUCCESS, $this->tester->getStatusCode());
    }

    /**
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccessSetSateAbsent()
    {
        $expectedResults = [
            'empty' => ['db_value' => null, 'value' => '1', 'expected' => null],
            'exists' => ['db_value' => '2', 'value' => '3', 'expected' => null],
            'next_set' => ['db_value' => '4', 'value' => '5', 'expected' => null]
        ];

        $this->runTestState($expectedResults, Config::STATE_ABSENT);
    }

    /**
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccessSetSateIgnored()
    {
        $expectedResults = [
            'empty' => ['db_value' => null, 'value' => '1', 'expected' => null],
            'exists' => ['db_value' => '2', 'value' => '3', 'expected' => '2'],
            'next_set' => ['db_value' => '4', 'value' => '5', 'expected' => '4']
        ];

        $this->runTestState($expectedResults, Config::STATE_IGNORED);
    }

    /**
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccessSetSateAlways()
    {
        $expectedResults = [
            'empty' => ['db_value' => null, 'value' => '1', 'expected' => '1'],
            'exists' => ['db_value' => '2', 'value' => '3', 'expected' => '3'],
            'next_set' => ['db_value' => '4', 'value' => '5', 'expected' => '5']
        ];

        $this->runTestState($expectedResults, Config::STATE_ALWAYS);
    }

    /**
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccessSetSateInit()
    {
        $expectedResults = [
            'empty' => ['db_value' => null, 'value' => '1', 'expected' => '1'],
            'exists' => ['db_value' => '2', 'value' => '3', 'expected' => '2'],
            'next_set' => ['db_value' => '4', 'value' => '5', 'expected' => '4']
        ];

        $this->runTestState($expectedResults, Config::STATE_INIT);
    }

    /**
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccessSetSateOnce()
    {
        $expectedResults = [
            'empty' => ['db_value' => null, 'value' => '1', 'expected' => '1'],
            'exists' => ['db_value' => '2', 'value' => '3', 'expected' => '3'],
            'next_set' => ['db_value' => '4', 'value' => '5', 'expected' => '4']
        ];

        $this->runTestState($expectedResults, Config::STATE_ONCE);
    }

    /**
     * @throws FileSystemException
     */
    public function testMergeFiles()
    {
        $files = [];
        for ($i = 1; $i <= 3; $i++) {
            $files[$i] = $this->newCsvFile('test' . $i . '.csv',
                $this->getDefaultCsvData(Config::STATE_ALWAYS, $i, 'merge/files/' . $i));
        }

        $this->tester->execute(['files' => $files]);

        foreach ($files as $i => $file) {
            $this->assertEquals($i, $this->scopeConfig->getValue('merge/files/' . $i));
        }
    }

    /**
     * @param string $csvFileName
     * @param array $csvData
     * @return string
     * @throws FileSystemException
     */
    protected function newCsvFile(string $csvFileName, array $csvData): string
    {
        $csv = $this->objectManager->get(Csv::class);
        $varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $path = $varDirectory->getAbsolutePath($csvFileName);
        $csv->appendData($varDirectory->getAbsolutePath($path), $csvData);
        return $path;
    }

    /**
     * @param string $state
     * @param string $value
     * @param string $configPath
     * @param string $code
     * @return array[]
     */
    protected function getDefaultCsvData(
        string $state,
        string $value = '1',
        $configPath = self::DEFAULT_CONFIG_PATH,
        $code = ''
    ): array {
        return [
            'header' => self::CSV_HEADERS,
            'row' => [
                'config_path' => $configPath,
                'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                'code' => $code,
                'value' => $value,
                'state' => $state
            ]
        ];
    }

    /**
     * @param $dbSate
     * @param $dbValue
     * @throws FileSystemException
     */
    protected function setBaseConfig($dbSate, $dbValue): void
    {
        $this->configWriter->delete(self::DEFAULT_CONFIG_PATH);
        if ($dbValue !== null) {
            $this->setBaseConfigInDb($dbSate, $dbValue);
        }
    }

    /**
     * @param $dbSate
     * @param $dbValue
     * @throws FileSystemException
     */
    protected function setBaseConfigInDb($dbSate, $dbValue): void
    {
        if ($dbSate == 'next_set') {
            $this->tester->execute([
                'files' => [
                    $this->newCsvFile(self::DEFAULT_CSV_FILE_NAME,
                        $this->getDefaultCsvData(Config::STATE_ALWAYS, $dbValue))
                ]
            ]);
        } else {
            $this->configWriter->save(self::DEFAULT_CONFIG_PATH, $dbValue,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        }
    }

    /**
     * @param array $expectedResults
     * @param string $state
     * @throws FileSystemException
     */
    protected function runTestState(array $expectedResults, string $state): void
    {
        foreach ($expectedResults as $dbSate => $expectedResult) {

            $this->setBaseConfig($dbSate, $expectedResult['db_value']);

            $this->tester->execute([
                'files' => [
                    $this->newCsvFile(
                        self::DEFAULT_CSV_FILE_NAME,
                        $this->getDefaultCsvData($state, $expectedResults[$dbSate]['value'])
                    )
                ]
            ]);

            $this->assertEquals(
                $expectedResults[$dbSate]['expected'],
                $this->scopeConfig->getValue(self::DEFAULT_CONFIG_PATH)
            );
        }
    }

    /**
     * @return StoreInterface
     */
    protected function getStore()
    {
        $store = null;
        $storeId = 1;
        while ($store == null) {
            try {
                $store = $this->storeManager->getStore($storeId);
            } catch (NoSuchEntityException $e) {
                $storeId++;
            }
        }
        return $store;
    }
}


