<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

#[\Symfony\Component\Console\Attribute\AsCommand(
	name: 'spameri:elastic:delete-index',
	description: 'Deletes index by name or alias. Warning this deletes your data!',
)]
class DeleteIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var string
	 */
	protected static $defaultName = 'spameri:elastic:delete-index';


	public function __construct(
		private readonly \Spameri\Elastic\Model\Indices\Delete $delete,
	)
	{
		parent::__construct(self::$defaultName);
	}


	protected function configure(): void
	{
		$this
			->setName(self::$defaultName)
			->setDescription('Deletes index by name or alias. Warning this deletes your data!')
			->addArgument('indexName', \Symfony\Component\Console\Input\InputArgument::IS_ARRAY)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output,
	): int
	{
		/** @var array<string> $indexNames */
		$indexNames = $input->getArgument('indexName');
		$output->writeln('Starting');

		foreach ($indexNames as $indexName) {
			$this->delete->execute($indexName);
		}

		$output->writeln('Done');

		return 0;
	}

}
