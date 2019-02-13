<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class UpdateMapping extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var array
	 */
	private $entities;

	/**
	 * @var \Spameri\Elastic\Model\Mapping
	 */
	private $mapping;


	public function __construct(
		array $entities
		, \Spameri\Elastic\Model\Mapping $mapping
	)
	{
		parent::__construct(NULL);
		$this->entities = $entities;
		$this->mapping = $mapping;
	}


	protected function configure() : void
	{
		$this
			->setName('spameri:elastic:update-mapping')
			->setDescription('Updates mapping in index for entity.')
			->addArgument('entityName')
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		/** @var string $entityName */
		$entityName = $input->getArgument('entityName');
		$output->writeln('Starting');

		if (isset($this->entities[$entityName])) {
			$this->mapping->updateMapping($this->entities[$entityName]);
		}

		$output->writeln('Done');
	}

}
