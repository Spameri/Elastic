<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class SetUpElasticCommand extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var array
	 */
	private $entities;

	/**
	 * @var \Spameri\Elastic\Mapper\ElasticMapper
	 */
	private $elasticMapper;


	public function __construct(
		$entities
		, \Spameri\Elastic\Mapper\ElasticMapper $elasticMapper
	)
	{
		parent::__construct(NULL);
		$this->entities = $entities;
		$this->elasticMapper = $elasticMapper;
	}


	protected function configure() : void
	{
		$this
			->setName('elastic:set-up')
			->setDescription('Create elastic index and import data types from entities.')
			->addOption('force', 'f');
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		if ($input->getOption('force')) {
			$this->elasticMapper->deleteIndex();
		}

		$this->elasticMapper->createIndex();

		foreach ($this->entities as $entity) {
			$this->elasticMapper->createMapping($entity);
		}
	}
}
