<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\ResourceModel\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Lingaro\Config\Model\Config as ConfigModel;
use Lingaro\Config\Model\ResourceModel\Config as ConfigResourceModel;

/**
 * Class Collection
 * @package Lingaro\Config\Model\ResourceModel\Config
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
