<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

#[\Symfony\Component\Console\Attribute\AsCommand(
	name: 'spameri:elastic:initialize-index',
	description: 'Creates index/es. And initializes with settings and mappings',
)]
class InitializeIndexes extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var string
	 */
	protected static $defaultName = 'spameri:elastic:initialize-index';


	public function __construct(
		private readonly \Spameri\Elastic\Model\EntitySettingsLocator $entitySettingsLocator,
		private readonly \Spameri\Elastic\Model\DeleteIndex $deleteIndex,
		private readonly \Spameri\Elastic\Model\InitializeIndex $initializeIndex,
	)
	{
		parent::__construct(self::$defaultName);
	}


	protected function configure(): void
	{
		$this
			->setName(self::$defaultName)
			->setDescription(
				'Creates index/es. And initializes with settings and mappings',
			)
			->addArgument('entityName', \Symfony\Component\Console\Input\InputArgument::IS_ARRAY)
			->addOption(
				'force', 'f', null,
				'Warning this deletes your data! Forces now used index to be deleted before new index is created.',
			)
		;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\AbstractElasticSearchException
	 */
	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output,
	): int
	{
		/** @var array<string> $entityNames */
		$entityNames = $input->getArgument('entityName');
		$forcedDelete = $input->getOption('force');

		$indexConfigs = $this->entitySettingsLocator->locateAll();

		foreach ($indexConfigs as $indexConfig) {
			$settings = $indexConfig->provide();

			foreach ($entityNames as $entityName) {
				if (\strpos($settings->indexName(), $entityName) === false) {
					continue 2;
				}
			}

			if ($forcedDelete) {
				try {
					$this->deleteIndex->execute($settings->indexName());
					$output->writeln('Index ' . $settings->indexName() . ' deleted.');

				} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
					// May be non existing index exception
				}
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
