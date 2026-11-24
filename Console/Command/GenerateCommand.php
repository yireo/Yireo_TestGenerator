<?php declare(strict_types=1);

namespace Yireo\TestGenerator\Console\Command;

use Magento\Framework\App\State as AppState;
use Magento\Framework\Component\ComponentRegistrar;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yireo\TestGenerator\Generator\UnitTest\UnitTestGenerator;
use Yireo\TestGenerator\Generator\IntegrationTest\IntegrationTestGenerator;

class GenerateCommand extends Command
{
    public function __construct(
        private IntegrationTestGenerator $integrationTestGenerator,
        private UnitTestGenerator $unitTestGenerator,
        private ComponentRegistrar $componentRegistrar,
        private AppState $appState,
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
            ->addArgument('className', InputArgument::OPTIONAL, 'Class name')
            ->addOption('override-existing', null, InputOption::VALUE_OPTIONAL, 'Override existing tests', false)
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of tests (unit, integration)', 'integration')
            ->addOption('generate-source-tests', null, InputOption::VALUE_OPTIONAL, 'Generate source tests', '1')
            ->addOption('generate-additional-tests', null, InputOption::VALUE_OPTIONAL, 'Generate additional tests', '1')
            ->addOption('ai', null, InputOption::VALUE_NONE, 'Allow sending source code to the configured AI provider')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->appState->setAreaCode('frontend');

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

        $overrideExisting = $this->getBooleanOption($input, 'override-existing');
        $generateSourceTests = $this->getBooleanOption($input, 'generate-source-tests');
        $generateAdditionalTests = $this->getBooleanOption($input, 'generate-additional-tests');
        if (null === $overrideExisting || null === $generateSourceTests || null === $generateAdditionalTests) {
            $output->writeln('<error>Invalid boolean value for an option</error>');
            return Command::INVALID;
        }

        $type = (string)$input->getOption('type');
        if (false === in_array($type, ['unit', 'integration'])) {
            $output->writeln('<error>Unsupported type</error>');
            return Command::INVALID;
        }

        $className = ltrim((string)$input->getArgument('className'), '\\');
        if ($className !== '' && false === $this->isModuleClass($className, $path)) {
            $output->writeln('<error>Class name is not a valid class of module '.$moduleName.'</error>');
            return Command::INVALID;
        }

        $allowAi = (bool)$input->getOption('ai');

        if ($type === 'integration' && !empty($className)) {
            $this->integrationTestGenerator->generateTestPerClass($moduleName, $className, $output, $overrideExisting);
        }

        if ($type === 'integration' && $generateSourceTests && empty($className)) {
            $this->integrationTestGenerator->generateSourceTests($moduleName, $output, $overrideExisting);
        }

        if ($type === 'integration' && $generateAdditionalTests && empty($className)) {
            $this->integrationTestGenerator->generateAdditionalTests($moduleName, $output, $overrideExisting);
        }

        if ($type === 'unit' && !empty($className)) {
            $this->unitTestGenerator->generateTestPerClass(
                $moduleName,
                $className,
                $output,
                $overrideExisting,
                $allowAi
            );
        }

        if ($type === 'unit' && empty($className)) {
            $this->unitTestGenerator->generateAll($moduleName, $output, $overrideExisting, $allowAi);
        }

        return Command::SUCCESS;
    }

    private function getBooleanOption(InputInterface $input, string $name): ?bool
    {
        $value = $input->getOption($name);
        if (null === $value) {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }

    private function isModuleClass(string $className, string $modulePath): bool
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)+$/', $className)) {
            return false;
        }

        if (false === class_exists($className)) {
            return false;
        }

        $classFile = realpath((string)(new ReflectionClass($className))->getFileName());
        $modulePath = realpath($modulePath);
        if (false === $classFile || false === $modulePath) {
            return false;
        }

        return str_starts_with($classFile, rtrim($modulePath, '/').'/');
    }
}
