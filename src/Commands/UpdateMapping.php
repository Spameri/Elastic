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
		$entities
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
			->setDescription('Updates mapping for entity.')
			->addArgument('entityName')
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		throw new \Nette\NotImplementedException('This command is not implemented');

		$entityName = $input->getArgument('entityName');

		$this->mapping->updateMapping($this->entities[$entityName]);
	}
}
