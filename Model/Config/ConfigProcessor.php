<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

declare(strict_types=1);

namespace Orba\Config\Model\Config;

use Exception;
use Orba\Config\Api\ConfigInterface;
use Orba\Config\Model\Config\OperationsRegistry\ConfigChange;

class ConfigProcessor
{
    /** @var ConfigRepository */
    private ConfigRepository $configRepository;

    /**
     * ConfigProcessor constructor.
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @param OperationsRegistry $operationsRegistry
     * @throws Exception
     */
    public function process(OperationsRegistry $operationsRegistry): void
    {
        $this->configRepository->insertConfigs($operationsRegistry->getToAddConfigs());
        $configsToUpdate = array_map(
            function (ConfigChange $configChange): ConfigInterface {
                return $configChange->getNewConfig();
            },
            array_merge($operationsRegistry->getToUpdateConfigs(), $operationsRegistry->getToUpdateHashConfigs())
        );

        $this->configRepository->updateConfigs($configsToUpdate);
        $configsToRemove = array_map(
            function (ConfigChange $configChange): ConfigInterface {
                return $configChange->getOldConfig();
            },
            $operationsRegistry->getToRemoveConfigs()
        );

        $this->configRepository->removeConfigs($configsToRemove);
    }
}
