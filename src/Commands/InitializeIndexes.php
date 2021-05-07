<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

class InitializeIndexes extends \Symfony\Component\Console\Command\Command
{

	private \Nette\DI\Container $container;

	private \Spameri\Elastic\Model\Indices\Delete $delete;

	private \Spameri\Elastic\Model\InitializeIndex $initializeIndex;


	public function __construct(
		\Nette\DI\Container $container,
		\Spameri\Elastic\Model\Indices\Delete $delete,
		\Spameri\Elastic\Model\InitializeIndex $initializeIndex
	)
	{
		parent::__construct(NULL);
		$this->container = $container;
		$this->delete = $delete;
		$this->initializeIndex = $initializeIndex;
	}


	protected function configure(): void
	{
		$this
			->setName('spameri:elastic:initialize-index')
			->setDescription(
				'Creates index/es. And initializes with settings and mappings'
			)
			->addArgument('entityName', \Symfony\Component\Console\Input\InputArgument::IS_ARRAY)
			->addOption(
				'force', 'f', NULL,
				'Warning this deletes your data! Forces now used index to be deleted before new index is created.'
			)
		;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\AbstractElasticSearchException
	 */
	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	): int
	{
		/** @var array<string> $entityNames */
		$entityNames = $input->getArgument('entityName');
		$forcedDelete = $input->getOption('force');

		$indexConfigs = $this->container->getByType(\Spameri\Elastic\Settings\IndexConfigInterface::class);

		/** @var \Spameri\Elastic\Settings\IndexConfigInterface $indexConfig */
		foreach ($indexConfigs as $indexConfig) {
			$settings = $indexConfig->provide();

			foreach ($entityNames as $entityName) {
				if (\strpos($settings->indexName(), $entityName) === FALSE) {
					continue 2;
				}
			}

			if ($forcedDelete) {
				$this->delete->execute($settings->indexName());
				$output->writeln('Index ' . $settings->indexName() . ' deleted.');
			}


			try {
				// initialize
				$this->initializeIndex->execute($indexConfig);

				$output->writeln('Index ' . $settings->indexName() . ' created.');

			} catch (\Spameri\Elastic\Exception\AbstractElasticSearchException $exception) {
				$output->writeln($exception->getMessage());
			}
		}

		return 0;
	}

}
