<?php
/**
 * Copyright © 2023 Lingaro sp. z o.o. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Lingaro\Config\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Lingaro\Config\Model\Config\OperationsRegistry;
use Lingaro\Config\Model\Csv\MultiReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Lingaro\Config\Model\Config\ConfigRepository;
use Lingaro\Config\Model\Config\ConfigAnalyzer;
use Lingaro\Config\Model\Config\ConfigProcessor;
use Lingaro\Config\Model\Config\ConfigSummary;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

/**
 * Class ConfigCommand
 */
class ConfigCommand extends Command
{
    public const COMMAND_NAME = 'lingaro:config';
    public const COMMAND_DESCRIPTION = '';

    public const OPTION_DRY_RUN = 'dry-run';
    public const OPTION_ENV = 'env';

    public const ARGUMENT_FILES = 'files';

    /** @var State */
    private $appState;

    /** @var MultiReader */
    private $csvReader;

    /** @var ConfigRepository */
    private $configRepository;

    /** @var ConfigAnalyzer */
    private $configAnalyzer;

    /** @var ConfigProcessor */
    private $configProcessor;

    /** @var CacheManager */
    private $cacheManager;

    /** @var EventManagerInterface */
    private $eventManager;

    /** @var ReinitableConfigInterface */
    private $reinitableConfig;

    /** @var ConfigSummary */
    private $configSummary;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * ConfigCommand constructor.
     * @param ConfigAnalyzer $configAnalyzer
     * @param CacheManager $cacheManager
     * @param EventManagerInterface $eventManager
     * @param ReinitableConfigInterface $reinitableConfig
     * @param ConfigSummary $configSummary
     * @param ObjectManagerInterface $objectManager
     * @param string|null $name
     */
    public function __construct(
        ConfigAnalyzer $configAnalyzer,
        CacheManager $cacheManager,
        EventManagerInterface $eventManager,
        ReinitableConfigInterface $reinitableConfig,
        ConfigSummary $configSummary,
        ObjectManagerInterface $objectManager,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->configAnalyzer = $configAnalyzer;
        $this->cacheManager = $cacheManager;
        $this->reinitableConfig = $reinitableConfig;
        $this->eventManager = $eventManager;
        $this->configSummary = $configSummary;
        $this->objectManager = $objectManager;
    }

    /** @inheritDoc */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription(self::COMMAND_DESCRIPTION)
            ->addOption(
                self::OPTION_ENV,
                null,
                InputOption::VALUE_OPTIONAL,
                'Specify env for which config values are used'
            )
            ->addOption(
                self::OPTION_DRY_RUN,
                null,
                InputOption::VALUE_NONE,
                'Set the option to get more details about result'
            )
            ->addArgument(
                self::ARGUMENT_FILES,
                InputArgument::IS_ARRAY,
                'Specify all config files to be used'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /* we need to add those classes to objectManager bacuse of issue with integration tests like this:
            magento-integration_1  |   [Magento\Framework\Setup\Exception]
            magento-integration_1  |   Unable to apply patch Magento\InventorySales\Setup\Patch\Schema\InitializeW
            magento-integration_1  |   ebsiteDefaultSock for module Magento_InventorySales. Original exception mes
            magento-integration_1  |   sage: The default website isn't defined. Set the website and try again.
            */
            $this->appState = $this->objectManager->get(State::class);
            $this->csvReader = $this->objectManager->get(MultiReader::class);
            $this->configRepository = $this->objectManager->get(ConfigRepository::class);
            $this->configProcessor = $this->objectManager->get(ConfigProcessor::class);

            // Set setup code go in setup mode
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
            $files = $input->getArgument(self::ARGUMENT_FILES);
            if (count($files) === 0) {
                throw new LocalizedException(__('Please specify at least one file with configuration'));
            }
            $csvConfigs = $this->csvReader->readConfigFiles(
                $files,
                $input->getOption(self::OPTION_ENV)
            );
            $dbConfigs = $this->configRepository->getAllConfigs();
            $operationsRegistry = $this->configAnalyzer->prepareConfigCollection($dbConfigs, $csvConfigs);

            if (!$input->getOption(self::OPTION_DRY_RUN)) {
                $this->configProcessor->process($operationsRegistry);
                $this->refreshCache();
            }
            $this->printSummary($output, $operationsRegistry);
        } catch (\Exception $e) {
            $output->writeln('<error>'. $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>'. 'Configuration has been updated successfully' . '</info>');
        return CLI::RETURN_SUCCESS;
    }

    /**
     * @param OutputInterface $output
     * @param OperationsRegistry $operationsRegistry
     */
    private function printSummary(OutputInterface $output, OperationsRegistry $operationsRegistry) : void
    {
        foreach ($this->configSummary->getTotals($operationsRegistry) as $key => $count) {
            $output->writeln(sprintf(
                '<info>%s: %d</info>',
                $key,
                $count
            ));
        }

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            foreach ($this->configSummary->getList($operationsRegistry) as $key => $configs) {
                if (empty($configs)) {
                    continue;
                }
                $output->writeln('');
                $output->writeln(sprintf('<info>%s:</info>', $key));
                foreach ($configs as $config) {
                    $output->writeln(sprintf('<info>%s</info>', $config));
                }
            }
            $output->writeln('');
        }
    }

    /**
     * Refresh cache
     */
    private function refreshCache() : void
    {
        $this->eventManager->dispatch('adminhtml_cache_flush_all');
        $types = $this->cacheManager->getAvailableTypes();
        $this->cacheManager->flush($types);
        $this->reinitableConfig->reinit();
    }
}
