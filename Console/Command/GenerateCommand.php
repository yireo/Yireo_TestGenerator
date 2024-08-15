<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Console\Command;

use Composer\Console\Input\InputArgument;
use Magento\Framework\Component\ComponentRegistrar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\UnitTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTestGenerator;

class GenerateCommand extends Command
{
    public function __construct(
        private IntegrationTestGenerator $integrationTestGenerator,
        private UnitTestGenerator $unitTestGenerator,
        private ComponentRegistrar $componentRegistrar,
        $name = null
    ) {
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('yireo:test:generate')
            ->setDescription('Generate tests for a given module')
            ->addArgument('moduleName', InputArgument::REQUIRED, 'Module name')
            ->addOption('override-existing', null, InputOption::VALUE_OPTIONAL, 'Override existing tests', false)
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of tests (unit, integration)', 'integration')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleName = (string)$input->getArgument('moduleName');
        if (empty($moduleName)) {
            $output->writeln('<error>No module name given as argument</error>');
            return Command::INVALID;
        }

        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (false === is_dir($path)) {
            $output->writeln('<error>Module name is not registered</error>');
            return Command::INVALID;
        }

        $overrideExisting = (bool)$input->getOption('override-existing');

        $type = (string)$input->getOption('type');
        if (false === in_array($type, ['unit', 'integration'])) {
            $output->writeln('<error>Unsupported type</error>');
            return Command::INVALID;
        }

        if ($type === 'integration') {
            $this->integrationTestGenerator->generate($moduleName, $output, $overrideExisting);
        }

        if ($type === 'unit') {
            $this->unitTestGenerator->generate($moduleName, $output, $overrideExisting);
        }

        return Command::SUCCESS;
    }
}
