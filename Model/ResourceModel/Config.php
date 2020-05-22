<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Orba\Config\Api\ConfigInterface;

/**
 * Class Config
 * @package Orba\Config\Model\ResourceModel
 * @codeCoverageIgnore
 */
class Config extends AbstractDb
{
    const TABLE_NAME = 'core_config_data';
    const TMP_TABLE_NAME = 'core_config_data_tmp';
    const ID_FIELD_NAME = 'config_id';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD_NAME);
    }

    /**
     * @param ConfigInterface[] $configs
     */
    public function bulkRemove(array $configs): void
    {
        if (empty($configs)) {
            return;
        }
        $ids = array_filter(
            array_map(
                function (ConfigInterface $config): ?string {
                    return $config->getConfigId();
                },
                $configs
            )
        );
        $connection = $this->getConnection();
        $connection->delete(
            $connection->getTableName(self::TABLE_NAME),
            [
                sprintf('%s IN(?)', self::ID_FIELD_NAME) => $ids
            ]
        );
    }

    /**
     * @param ConfigInterface[] $configs
     */
    public function bulkInsert(array $configs): void
    {
        if (empty($configs)) {
            return;
        }
        $configsAsArray = array_map(
            function ($configModel) {
                return $configModel->getAllData();
            },
            $configs
        );
        $connection = $this->getConnection();
        $connection->insertMultiple(
            $connection->getTableName(self::TABLE_NAME),
            $configsAsArray
        );
    }

    /**
     * @param ConfigInterface[] $configs
     */
    public function bulkUpdate(array $configs): void
    {
        if (empty($configs)) {
            return;
        }
        $configsAsArray = array_map(
            function ($configModel) {
                return $configModel->getAllData();
            },
            $configs
        );
        $connection = $this->getConnection();
        $tableName = $connection->getTableName(self::TABLE_NAME);
        $tmpTableName = $connection->getTableName(self::TMP_TABLE_NAME);
        $connection->dropTemporaryTable($tmpTableName);
        $connection->createTemporaryTableLike($tmpTableName, $tableName);
        $connection->insertMultiple(
            $tmpTableName,
            $configsAsArray
        );
        $select = $connection->select()
            ->from(
                [$tableName],
                []
            )
            ->joinInner(
                ['tmp_table' => $tmpTableName],
                '(`main_table`.`scope` = `tmp_table`.`scope`)'
                . 'AND (`main_table`.`scope_id` = `tmp_table`.`scope_id`)'
                . 'AND (`main_table`.`path` = `tmp_table`.`path`)',
                ['value', 'imported_value_hash']
            );
        $query = $connection->updateFromSelect(
            $select,
            ['main_table' => $tableName]
        );
        $connection->query($query);
        $connection->dropTemporaryTable($tmpTableName);
    }
}
