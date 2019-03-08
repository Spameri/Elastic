<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class DumpIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var \Spameri\Elastic\Model\DumpIndex
	 */
	private $dumpIndex;


	public function __construct(
		\Spameri\Elastic\Model\DumpIndex $migrate
	)
	{
		parent::__construct(NULL);
		$this->dumpIndex = $migrate;
	}


	/**
	 * @example spameri:elastic:dump-index index elasticDump.dump
	 */
	protected function configure() : void
	{
		$this
			->setName('spameri:elastic:dump-index')
			->setDescription('Dumps all data from index to file')
			->addArgument('index', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('filename', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		$output->writeln('Starting');

		$index = $input->getArgument('index');
		$filename = $input->getArgument('filename');

		$this->dumpIndex->setOutput($output);
		$this->dumpIndex->execute($index, $filename);

		$output->writeln('Done');
	}

}
