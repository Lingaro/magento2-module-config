<?php
/**
 * @copyright Copyright (c) 2020 Orba Sp. z o.o. (http://orba.co)
 */

namespace Orba\Config\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Orba\Config\Model\Csv\MultiReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\Cli;
use Orba\Config\Model\Config\ConfigRepository;
use Orba\Config\Model\Config\ConfigAnalyzer;
use Orba\Config\Model\Config\ConfigProcessor;

use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

class ConfigCommand extends Command
{
    public const COMMAND_NAME = 'orba:config';
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

    /**
     * ConfigCommand constructor.
     * @param State $appState
     * @param MultiReader $csvReader
     * @param ConfigRepository $configRepository
     * @param ConfigAnalyzer $configAnalyzer
     * @param ConfigProcessor $configProcessor
     * @param CacheManager $cacheManager
     * @param EventManagerInterface $eventManager
     * @param ReinitableConfigInterface $reinitableConfig
     * @param string|null $name
     */
    public function __construct(
        State $appState,
        MultiReader $csvReader,
        ConfigRepository $configRepository,
        ConfigAnalyzer $configAnalyzer,
        ConfigProcessor $configProcessor,
        CacheManager $cacheManager,
        EventManagerInterface $eventManager,
        ReinitableConfigInterface $reinitableConfig,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->appState = $appState;
        $this->csvReader = $csvReader;
        $this->configRepository = $configRepository;
        $this->configAnalyzer = $configAnalyzer;
        $this->configProcessor = $configProcessor;
        $this->cacheManager = $cacheManager;
        $this->reinitableConfig = $reinitableConfig;
        $this->eventManager = $eventManager;
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
        } catch (\Exception $e) {
            $output->writeln('<error>'. $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>'. 'Configuration has been updated successfully' . '</info>');
        return CLI::RETURN_SUCCESS;
    }

    private function refreshCache() : void
    {
        $this->eventManager->dispatch('adminhtml_cache_flush_all');
        $types = $this->cacheManager->getAvailableTypes();
        $this->cacheManager->flush($types);
        $this->reinitableConfig->reinit();
    }
}
