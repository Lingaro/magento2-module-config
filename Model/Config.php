<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Model;

class Config extends \Magento\Config\Model\Config
{
    public function getConfigId(): string
    {
        return $this->getData('config_id');
    }

    public function getPath(): string
    {
        return $this->getData('path');
    }

    public function getScope()
    {
        $this->load();
        return parent::getScope();
    }

    public function getScopeCode()
    {
        $this->load();
        return parent::getScopeCode();
    }
}
