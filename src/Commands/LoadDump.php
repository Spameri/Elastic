<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

class LoadDump extends \Symfony\Component\Console\Command\Command
{

	protected static $defaultName = 'spameri:elastic:load-dump';

	private \Spameri\Elastic\Model\RestoreIndex $restoreIndex;


	public function __construct(
		\Spameri\Elastic\Model\RestoreIndex $migrate,
	)
	{
		parent::__construct(NULL);
		$this->restoreIndex = $migrate;
	}


	/**
	 * @example spameri:elastic:load-dump
	 */
	protected function configure(): void
	{
		$this
			->setName(self::$defaultName)
			->setDescription('Loads data from provided dump file.')
			->addArgument('filename', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument(
				'step',
				\Symfony\Component\Console\Input\InputArgument::OPTIONAL,
				'Number of documents per one bulk index',
				'500',
			)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output,
	): int
	{
		$output->writeln('Starting');

		$filename = $input->getArgument('filename');
		$step = (int) $input->getArgument('step');

		$this->restoreIndex->setOutput($output);
		$this->restoreIndex->execute($filename, $step);

		$output->writeln('Done');

		return 0;
	}

}
