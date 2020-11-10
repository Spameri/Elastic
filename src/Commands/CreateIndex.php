<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class CreateIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var \Spameri\Elastic\Mapper\ElasticMapper
	 */
	private $elasticMapper;


	public function __construct(
		\Spameri\Elastic\Mapper\ElasticMapper $elasticMapper
	)
	{
		parent::__construct(NULL);
		$this->elasticMapper = $elasticMapper;
	}


	protected function configure(): void
	{
		$this
			->setName('spameri:elastic:create-index')
			->setDescription('Creates index')
			->addArgument('indexName', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
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
		/** @var string $indexName */
		$indexName = $input->getArgument('indexName');
		$forcedDelete = $input->getOption('force');

		if ($forcedDelete) {
			$this->elasticMapper->deleteIndex($indexName);
			$output->writeln('Index ' . $indexName . ' deleted.');
		}
		try {
			$this->elasticMapper->createIndex([
				'index' => $indexName,
			]);
			$output->writeln('Index ' . $indexName . ' created.');

		} catch (\Spameri\Elastic\Exception\AbstractElasticSearchException $exception) {
			$output->writeln($exception->getMessage());
		}

		return 0;
	}

}
