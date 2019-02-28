<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class TypeToNewIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var \Spameri\Elastic\Model\TypeToNewIndex\Migrate
	 */
	private $migrate;


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
	protected function configure() : void
	{
		$this
			->setName('spameri:elastic:move-type')
			->setDescription('Move type to new index to separate data and prepare for deprecation of types is ES.')
			->addArgument('indexFrom', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('typeFrom', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('indexTo', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('aliasTo', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addOption('allowClose', 'c', NULL,
				'Allows command to close index for data transfer. After data is transferred index is opened and resumes normal operations. When open it needs to check changed files after move and sync remaining.',
				TRUE
			)
		;
	}

	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		$output->writeln('Starting');

		$indexFrom = $input->getArgument('indexFrom');
		$typeFrom = $input->getArgument('typeFrom');
		$indexTo = $input->getArgument('indexTo');
		$aliasTo = $input->getArgument('aliasTo');
		$allowClose = $input->getOption('allowClose');

		$this->migrate->setOutput($output);
		$this->migrate->execute($indexFrom, $typeFrom, $indexTo, $aliasTo, $allowClose);

		$output->writeln('Done');
	}

}
