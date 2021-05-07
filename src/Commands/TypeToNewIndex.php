<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

class TypeToNewIndex extends \Symfony\Component\Console\Command\Command
{

	private \Spameri\Elastic\Model\TypeToNewIndex\Migrate $migrate;


	public function __construct(
		\Spameri\Elastic\Model\TypeToNewIndex\Migrate $migrate
	)
	{
		parent::__construct(NULL);
		$this->migrate = $migrate;
	}


	/**
	 * @example spameri:elastic:move-type oldIndex productType newIndex newIndexAlias -c
	 */
	protected function configure(): void
	{
		$this
			->setName('spameri:elastic:move-type')
			->setDescription('Move type to new index to separate data and prepare for deprecation of types is ES.')
			->addArgument('indexFrom', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('typeFrom', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('indexTo', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('aliasTo', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('typeTo', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Use only on old ElasticSearch', NULL)
			->addOption(
				'allowClose', 'c', \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
				'Allows command to close index for data transfer. After data is transferred index is opened and resumes normal operations. When open it needs to check changed files after move and sync remaining.',
				TRUE
			)
		;
	}


	/**
	 * @throws \Elasticsearch\Common\Exceptions\ElasticsearchException
	 */
	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output
	): int
	{
		$output->writeln('Starting');

		$indexFrom = $input->getArgument('indexFrom');
		$typeFrom = $input->getArgument('typeFrom');
		$indexTo = $input->getArgument('indexTo');
		$aliasTo = $input->getArgument('aliasTo');
		$typeTo = $input->getOption('typeTo');
		$allowClose = $input->getOption('allowClose');

		$this->migrate->setOutput($output);
		$this->migrate->execute((string) $indexFrom, (string) $typeFrom, (string) $indexTo, (string) $aliasTo, (string) $typeTo, (bool) $allowClose);

		$output->writeln('Done');

		return 0;
	}

}
