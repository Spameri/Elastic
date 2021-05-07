<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class CreateIndex extends \Symfony\Component\Console\Command\Command
{

	private \Spameri\Elastic\Model\CreateIndex $createIndex;

	private \Spameri\Elastic\Model\DeleteIndex $deleteIndex;


	public function __construct(
		\Spameri\Elastic\Model\CreateIndex $createIndex,
		\Spameri\Elastic\Model\DeleteIndex $deleteIndex
	)
	{
		parent::__construct(NULL);
		$this->createIndex = $createIndex;
		$this->deleteIndex = $deleteIndex;
	}


	protected function configure(): void
	{
		$this
			->setName('spameri:elastic:create-index')
			->setDescription(
				'Creates index. Take string as is, adds timestamp and inserts it in Elastic. No mapping or settings.'
			)
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
			$this->deleteIndex->execute($indexName);
			$output->writeln('Index ' . $indexName . ' deleted.');
		}

		try {
			$this->createIndex->execute($indexName);
			$output->writeln('Index ' . $indexName . ' created.');

		} catch (\Spameri\Elastic\Exception\AbstractElasticSearchException $exception) {
			$output->writeln($exception->getMessage());
		}

		return 0;
	}

}
