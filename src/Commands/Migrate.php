<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class Migrate extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @example spameri:elastic:load-dump
	 */
	protected function configure() : void
	{
		$this
			->setName('spameri:elastic:migrate')
			->setDescription('Runs migrations from files.')
			->addArgument('filename', \Symfony\Component\Console\Input\InputArgument::OPTIONAL)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	): int
	{
		$output->writeln('Starting');

		// 1. Get folder
		// 2. Iterate folder
		// 3. Run each file in folder
		// 3a. Check if file was executed - skip
		// 3b. Check if file was changed - skip and report
		// 4. Save executed files to ES
		// 5. Done

		return 0;
	}
}
