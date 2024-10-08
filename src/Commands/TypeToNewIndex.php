<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

#[\Symfony\Component\Console\Attribute\AsCommand(
	name: 'spameri:elastic:move-type',
	description: 'Move type to new index to separate data and prepare for deprecation of types is ES.'
)]
class TypeToNewIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var string
	 */
	protected static $defaultName = 'spameri:elastic:move-type';


	public function __construct(
		private readonly \Spameri\Elastic\Model\TypeToNewIndex\Migrate $migrate,
	)
	{
		parent::__construct(self::$defaultName);
	}


	/**
	 * @example spameri:elastic:move-type oldIndex productType newIndex newIndexAlias -c
	 */
	protected function configure(): void
	{
		$this
			->setName(self::$defaultName)
			->setDescription('Move type to new index to separate data and prepare for deprecation of types is ES.')
			->addArgument('indexFrom', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('indexTo', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('aliasTo', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addOption(
				'allowClose', 'c', \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
				'Allows command to close index for data transfer. After data is transferred index is opened and resumes normal operations. When open it needs to check changed files after move and sync remaining.',
				true,
			)
		;
	}


	/**
	 * @throws \Elastic\Elasticsearch\Exception\ElasticsearchException
	 */
	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output,
	): int
	{
		$output->writeln('Starting');

		$indexFrom = $input->getArgument('indexFrom');
		$indexTo = $input->getArgument('indexTo');
		$aliasTo = $input->getArgument('aliasTo');
		$allowClose = $input->getOption('allowClose');

		$this->migrate->setOutput($output);
		$this->migrate->execute(
			indexFrom: (string) $indexFrom,
			indexTo: (string) $indexTo,
			aliasTo: (string) $aliasTo,
			allowClose: (bool) $allowClose,
		);

		$output->writeln('Done');

		return 0;
	}

}
