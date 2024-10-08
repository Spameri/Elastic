<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

class DumpIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var string
	 */
	protected static $defaultName = 'spameri:elastic:dump-index';


	public function __construct(
		private readonly \Spameri\Elastic\Model\DumpIndex $migrate,
	)
	{
		parent::__construct(self::$defaultName);
	}


	/**
	 * @example spameri:elastic:dump-index index elasticDump.dump
	 */
	protected function configure(): void
	{
		$this
			->setName(self::$defaultName)
			->setDescription('Dumps all data from index to file')
			->addArgument('index', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('filename', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,

		\Symfony\Component\Console\Output\OutputInterface $output,
	): int
	{
		$output->writeln('Starting');

		$index = $input->getArgument('index');
		$filename = $input->getArgument('filename');

		$this->migrate->setOutput($output);
		$this->migrate->execute($index, $filename);

		$output->writeln('Done');

		return 0;
	}

}
