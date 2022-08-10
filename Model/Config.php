<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model;

use Magento\Config\Model\Config as ParentConfig;
use Orba\Config\Api\ConfigInterface;

/**
 * Class Config
 * @package Orba\Config\Model
 * @codeCoverageIgnore
 */
class Config extends ParentConfig implements ConfigInterface
{
    /**
     * @return string|null
     */
    public function getConfigId(): ?string
    {
        return $this->getData('config_id');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->getData('path');
    }

    /**
     * @return string
     */
    public function getScopeType(): string
    {
        $this->load();
        return parent::getScope();
    }

    /**
     * @return string|null
     */
    public function getScopeCode(): ?string
    {
        $this->load();
        return parent::getScopeCode();
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->getData('value');
    }

    /**
     * @return int|null
     */
    public function getScopeId(): ?int
    {
        $scopeId = $this->getData('scope_id');
        return $scopeId === null ? $scopeId : (int) $scopeId;
    }

    /**
     * @return string
     */
    public function getimportedValueHash(): string
    {
        return $this->getData('imported_value_hash') ?? '';
    }

    /**
     * @return array
     */
    public function getAllData() : array
    {
        return $this->getData();
    }
}
