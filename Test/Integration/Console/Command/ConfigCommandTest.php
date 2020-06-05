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
use Orba\Config\Model\Config\ConfigRepository;

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

    /** @var ConfigRepository */
    private $configRepository;


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
        $this->configRepository = $this->objectManager->get(ConfigRepository::class);
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
     * @dataProvider providerForTestCommandReturnsSuccessAndActsAccordingToState
     * @param array $db
     * @param array $csv
     * @param array $expected
     * @throws FileSystemException
     */
    public function testCommandReturnsSuccessAndActsAccordingToState(array $db, array $csv, array $expected)
    {
        $this->setBaseConfig(
            '',
            $db['value'],
            $db['imported_value_hash']
        );

        $csvData = $this->getDefaultCsvData($csv['state'], $csv['value']);

        $this->tester->execute([
            'files' => [
                $this->newCsvFile(
                    self::DEFAULT_CSV_FILE_NAME,
                    $csvData
                )
            ]
        ]);

        $this->assertEquals(
            $expected['value'],
            $this->scopeConfig->getValue(self::DEFAULT_CONFIG_PATH)
        );

        $dbConfig = $this->configRepository->get(
            $csvData['row']['config_path'],
            $csvData['row']['scope'],
            $csvData['row']['code']
        );
        $actualHashInDb = $dbConfig->getimportedValueHash();

        if (!$expected['imported_value_hash']) {
            $this->assertEquals(
                '',
                $actualHashInDb,
                sprintf(
                    'expected no imported_value_hash, actual value in database is %s.'
                    . ' This may mean that importer saved config although it was not necessary',
                    $actualHashInDb
                )
            );
        } else {
            $expectedHash = sha1($expected['imported_value_hash']);
            $this->assertEquals(
                $expectedHash,
                $actualHashInDb,
                sprintf(
                    'expected imported_value_hash is %s, actual value in database is %s.'
                    . ' Importer is supposed to insert/update hash based on last imported value'
                    . ', also when value in database is the same as value in csv but imported_value_hash is missing',
                    $expectedHash,
                    $actualHashInDb
                )
            );
        }
    }

    /**
     * @return array[]
     */
    public function providerForTestCommandReturnsSuccessAndActsAccordingToState() : array
    {
        return [
            'state=absent;db=empty' => [
                'db' => [
                    'value' => null,
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '1',
                    'state' => Config::STATE_ABSENT
                ],
                'expected' => [
                    'value' => null,
                    'imported_value_hash' => null
                ]
            ],
            'state=absent;db=exists_manual' => [
                'db' => [
                    'value' => '2',
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '3',
                    'state' => Config::STATE_ABSENT
                ],
                'expected' => [
                    'value' => null,
                    'imported_value_hash' => null
                ]
            ],
            'state=absent;db=exists_imported' => [
                'db' => [
                    'value' => '4',
                    'imported_value_hash' => '4'
                ],
                'csv' => [
                    'value' => '5',
                    'state' => Config::STATE_ABSENT
                ],
                'expected' => [
                    'value' => null,
                    'imported_value_hash' => null
                ]
            ],
            'state=ignored;db=empty' => [
                'db' => [
                    'value' => null,
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '1',
                    'state' => Config::STATE_IGNORED
                ],
                'expected' => [
                    'value' => null,
                    'imported_value_hash' => null
                ]
            ],
            'state=ignored;db=exists_manual' => [
                'db' => [
                    'value' => '2',
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '3',
                    'state' => Config::STATE_IGNORED
                ],
                'expected' => [
                    'value' => '2',
                    'imported_value_hash' => null
                ]
            ],
            'state=ignored;db=exists_imported' => [
                'db' => [
                    'value' => '4',
                    'imported_value_hash' => '4'
                ],
                'csv' => [
                    'value' => '5',
                    'state' => Config::STATE_IGNORED
                ],
                'expected' => [
                    'value' => '4',
                    'imported_value_hash' => '4'
                ]
            ],
            'state=always;db=empty' => [
                'db' => [
                    'value' => null,
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '1',
                    'state' => Config::STATE_ALWAYS
                ],
                'expected' => [
                    'value' => '1',
                    'imported_value_hash' => '1'
                ]
            ],
            'state=always;db=exists_manual' => [
                'db' => [
                    'value' => '2',
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '3',
                    'state' => Config::STATE_ALWAYS
                ],
                'expected' => [
                    'value' => '3',
                    'imported_value_hash' => '3'
                ]
            ],
            'state=always;db=exists_imported' => [
                'db' => [
                    'value' => '4',
                    'imported_value_hash' => '4'
                ],
                'csv' => [
                    'value' => '5',
                    'state' => Config::STATE_ALWAYS
                ],
                'expected' => [
                    'value' => '5',
                    'imported_value_hash' => '5'
                ]
            ],
            'state=init;db=empty' => [
                'db' => [
                    'value' => null,
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '1',
                    'state' => Config::STATE_INIT
                ],
                'expected' => [
                    'value' => '1',
                    'imported_value_hash' => '1'
                ]
            ],
            'state=init;db=exists_manual' => [
                'db' => [
                    'value' => '2',
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '3',
                    'state' => Config::STATE_INIT
                ],
                'expected' => [
                    'value' => '2',
                    'imported_value_hash' => null
                ]
            ],
            'state=init;db=exists_imported' => [
                'db' => [
                    'value' => '4',
                    'imported_value_hash' => '4'
                ],
                'csv' => [
                    'value' => '5',
                    'state' => Config::STATE_INIT
                ],
                'expected' => [
                    'value' => '4',
                    'imported_value_hash' => '4'
                ]
            ],
            'state=once;db=empty' => [
                'db' => [
                    'value' => null,
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '1',
                    'state' => Config::STATE_ONCE
                ],
                'expected' => [
                    'value' => '1',
                    'imported_value_hash' => '1'
                ]
            ],
            'state=once;db=exists_manual' => [
                'db' => [
                    'value' => '2',
                    'imported_value_hash' => null
                ],
                'csv' => [
                    'value' => '3',
                    'state' => Config::STATE_ONCE
                ],
                'expected' => [
                    'value' => '3',
                    'imported_value_hash' => '3'
                ]
            ],
            'state=once;db=exists_imported_and_manual;hash_different' => [
                'db' => [
                    'value' => '4',
                    'imported_value_hash' => '3'
                ],
                'csv' => [
                    'value' => '5',
                    'state' => Config::STATE_ONCE
                ],
                'expected' => [
                    'value' => '5',
                    'imported_value_hash' => '5'
                ]
            ],
            'state=once;db=exists_imported_and_manual;hash_same' => [
                'db' => [
                    'value' => '4',
                    'imported_value_hash' => '3'
                ],
                'csv' => [
                    'value' => '3',
                    'state' => Config::STATE_ONCE
                ],
                'expected' => [
                    'value' => '4',
                    'imported_value_hash' => '3'
                ]
            ],
        ];
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
     * @param null $previousImportValue
     * @throws FileSystemException
     */
    protected function setBaseConfig($dbSate, $dbValue, $previousImportValue = null): void
    {
        $this->configWriter->delete(self::DEFAULT_CONFIG_PATH);

        if ($dbValue === null) {
            return;
        }

        if ($previousImportValue) {
            $this->tester->execute([
                'files' => [
                    $this->newCsvFile(self::DEFAULT_CSV_FILE_NAME,
                        $this->getDefaultCsvData(Config::STATE_ALWAYS, $previousImportValue))
                ]
            ]);
        }

        $this->configWriter->save(
            self::DEFAULT_CONFIG_PATH,
            $dbValue,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
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


