<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class RestoreIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var \Spameri\Elastic\Model\RestoreIndex
	 */
	private $restoreIndex;


	public function __construct(
		\Spameri\Elastic\Model\RestoreIndex $migrate
	)
	{
		parent::__construct(NULL);
		$this->restoreIndex = $migrate;
	}


	/**
	 * @example spameri:elastic:restore-index
	 */
	protected function configure() : void
	{
		$this
			->setName('spameri:elastic:restore-index')
			->setDescription('Restores data from provided dump file.')
			->addArgument('filename', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument(
				'step',
				\Symfony\Component\Console\Input\InputArgument::OPTIONAL,
				'Number of documents per one bulk index',
				500
			)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		$output->writeln('Starting');

		$index = $input->getArgument('index');
		$step = (int) $input->getArgument('step');

		$this->restoreIndex->setOutput($output);
		$this->restoreIndex->execute($index, $step);

		$output->writeln('Done');
	}

}
