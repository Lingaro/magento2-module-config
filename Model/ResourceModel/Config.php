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
}
