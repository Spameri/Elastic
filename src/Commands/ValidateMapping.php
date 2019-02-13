<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class ValidateMapping extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var \Spameri\Elastic\Model\ValidateMapping
	 */
	private $validateMapping;


	public function __construct(
		\Spameri\Elastic\Model\ValidateMapping $validateMapping
	)
	{
		parent::__construct(NULL);
		$this->validateMapping = $validateMapping;
	}


	protected function configure() : void
	{
		$this
			->setName('spameri:elastic:validate-mapping')
			->setDescription('Validates all neon entity mappings.')
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input // phpcs:ignore
		, \Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		$output->writeln('Starting');

		$this->validateMapping->validate();

		$this->validateMapping->display($output);

		$output->writeln('Done');
	}

}
