<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class CreateEntityCommand extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var array
	 */
	private $entities;

	/**
	 * @var \Spameri\Elastic\Generator\FileBuilder
	 */
	private $fileBuilder;


	public function __construct(
		$entities
		, \Spameri\Elastic\Generator\FileBuilder $fileBuilder
	)
	{
		parent::__construct(NULL);
		$this->entities = $entities;
		$this->fileBuilder = $fileBuilder;
	}


	protected function configure() : void
	{
		$this
			->setName('elastic:generate-entities')
			->setDescription('Generate entity files from configuration to file structure by namespace')
			->addArgument('entityClass', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Generate specific entity class.')
			->addOption('force', 'f');
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		$progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output);
		$progressBar->setFormat($progressBar::getFormatDefinition('debug'));

		$entityClass = $input->getArgument('entityClass');
		if ($entityClass) {
			if ($this->entities[$entityClass]) {
				$entity = $this->entities[$entityClass];
				$namespace = explode('\\', $entity['namespace']);
				if ($input->getOption('force') || ! file_exists(__DIR__ . '/../../../' . implode('/', $namespace) . '/' . $entity['class'] . '.php')) {
					$this->fileBuilder->buildForEntity($entityClass, $entity);
				}

			} else {
				$output->writeln($entityClass . ' is not configured in neon files.');
			}

		} else {
			$progressBar->start(count($this->entities));

			foreach ($this->entities as $class => $entity) {
				$namespace = explode('\\', $entity['namespace']);
				if ($input->getOption('force') || ! file_exists(__DIR__ . '/../../../' . implode('/', $namespace) . '/' . $entity['class'] . '.php')) {
					$this->fileBuilder->buildForEntity($class, $entity);
				}
				$progressBar->advance();
			}

			$progressBar->finish();
		}
	}
}
