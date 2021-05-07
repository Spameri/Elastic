<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class DeleteIndex extends \Symfony\Component\Console\Command\Command
{

	private \Spameri\Elastic\Model\Indices\Delete $delete;


	public function __construct(
		\Spameri\Elastic\Model\Indices\Delete $delete
	)
	{
		parent::__construct(NULL);
		$this->delete = $delete;
	}


	protected function configure(): void
	{
		$this
			->setName('spameri:elastic:delete-index')
			->setDescription('Deletes index by name or alias. Warning this deletes your data!')
			->addArgument('indexName', \Symfony\Component\Console\Input\InputArgument::IS_ARRAY)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	): int
	{
		/** @var array $indexNames */
		$indexNames = $input->getArgument('indexName');
		$output->writeln('Starting');

		foreach ($indexNames as $indexName) {
			$this->delete->execute($indexName);
		}

		$output->writeln('Done');

		return 0;
	}

}
