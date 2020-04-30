<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model\ResourceModel\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Orba\Config\Model\Config as ConfigModel;
use Orba\Config\Model\ResourceModel\Config as ConfigResourceModel;

/**
 * Class Collection
 * @package Orba\Config\Model\ResourceModel\Config
 * @codeCoverageIgnore
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            ConfigModel::class,
            ConfigResourceModel::class
        );
    }
}
