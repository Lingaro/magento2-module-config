<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Model\Config;

use Lingaro\Config\Api\ConfigInterface;
use Lingaro\Config\Model\Config\OperationsRegistry\ConfigChange;

/**
 * Class ConfigSummary
 */
class ConfigSummary
{
    /**
     * @param OperationsRegistry $operationsRegistry
     * @return array
     */
    public function getTotals(OperationsRegistry $operationsRegistry) : array
    {
        $result = [
            'Added' => count($operationsRegistry->getToAddConfigs()),
            'Updated' => count($operationsRegistry->getToUpdateConfigs()),
            'Updated Hash' => count($operationsRegistry->getToUpdateHashConfigs()),
            'Removed' => count($operationsRegistry->getToRemoveConfigs()),
            'Ignored' => count($operationsRegistry->getIgnoredConfigs())
        ];
        $result['Total'] = array_sum($result);

        return $result;
    }

    /**
     * @param OperationsRegistry $operationsRegistry
     * @return array
     */
    public function getList(OperationsRegistry $operationsRegistry) : array
    {
        return [
            'Added' => $this->prepareLines($operationsRegistry->getToAddConfigs()),
            'Updated' => $this->prepareLines($operationsRegistry->getToUpdateConfigs()),
            'Updated Hash' => $this->prepareLines($operationsRegistry->getToUpdateHashConfigs()),
            'Removed' => $this->prepareLines($operationsRegistry->getToRemoveConfigs()),
            'Ignored' => $this->prepareLines($operationsRegistry->getIgnoredConfigs())
        ];
    }

    /**
     * @param array $items
     * @return array
     */
    private function prepareLines(array $items) : array
    {
        $result = [];

        foreach ($items as $item) {
            if ($item instanceof ConfigChange) {
                $item = $item->getNewConfig();
            }
            $result[] = $this->prepareLine($item);
        }

        return $result;
    }

    /**
     * @param ConfigInterface $config
     * @return string
     */
    private function prepareLine(ConfigInterface $config) : string
    {
        return sprintf(
            "%s\t%s\t%s",
            $config->getPath(),
            $config->getScopeType(),
            $config->getScopeCode()
        );
    }
}
