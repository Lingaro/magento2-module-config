<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ScopeInterface as FrameworkScope;

class ScopeMap
{
    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var array */
    private $scopeMap = [];

    /**
     * ScopeMap constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
        $this->initMap();
    }

    private function initMap(): void
    {
        foreach ($this->storeManager->getStores() as $storeId => $store) {
            $this->scopeMap[ScopeInterface::SCOPE_STORES][$store->getCode()] = $storeId;
        }

        foreach ($this->storeManager->getWebsites() as $websiteId => $website) {
            $this->scopeMap[ScopeInterface::SCOPE_WEBSITES][$website->getCode()] = $websiteId;
        }
    }

    /**
     * @param string $scope
     * @param string $code
     * @return int|null
     */
    public function getIdByScopeAndCode(string $scope, string $code) : ?int
    {
        if (empty($scope) || $scope === FrameworkScope::SCOPE_DEFAULT) {
            return 0;
        }

        return $this->scopeMap[$scope][$code] ?? null;
    }
}
