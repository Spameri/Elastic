<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

class InitializeSettings extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var \Spameri\Elastic\Settings\IndexConfigInterface[]
	 */
	private $indexConfig;


	public function __construct(
		\Spameri\Elastic\Settings\IndexConfigInterface ... $indexConfig
	)
	{
		parent::__construct(NULL);
		$this->indexConfig = $indexConfig;
	}

	public function configure(): void
	{
		$this
			->setName('spameri:elastic:initialize-settings')
			->setDescription('Creates index and puts mapping and settings for entity/ies.')
		;
	}


	public function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output
	): int
	{
		$output->writeln('Starting');
		foreach ($this->indexConfig as $indexConfig) {
			$output->writeln('For index ' . $indexConfig->provide()->indexName());
//			$this->settinsCreator->create($indexConfig->provide());
		}

		return 0;
	}

}
