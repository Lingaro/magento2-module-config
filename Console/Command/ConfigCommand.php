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

    /**
     * ConfigCommand constructor.
     * @param State $appState
     * @param MultiReader $csvReader
     * @param string|null $name
     */
    public function __construct(State $appState, MultiReader $csvReader, ?string $name = null)
    {
        parent::__construct($name);
        $this->appState = $appState;
        $this->csvReader = $csvReader;
    }

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
                InputOption::VALUE_OPTIONAL,
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
            $configs = $this->csvReader->readConfigFiles(
                $files,
                $input->getOption(self::OPTION_ENV)
            );
        } catch (\Exception $e) {
            $output->writeln('<error>'. $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>'. 'Configuration has been updated successfully' . '</info>');
        return CLI::RETURN_SUCCESS;
    }
}
