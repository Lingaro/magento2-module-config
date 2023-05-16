<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model;

use Magento\Config\Model\Config as ParentConfig;
use Lingaro\Config\Api\ConfigInterface;

/**
 * Class Config
 * @package Lingaro\Config\Model
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
