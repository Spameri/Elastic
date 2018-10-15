<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class CreateMapping extends \Symfony\Component\Console\Command\Command
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
			->setName('spameri:elastic:create-mapping')
			->setDescription('Creates mapping for entity/ies.')
			->addArgument('entityName', \Symfony\Component\Console\Input\InputArgument::OPTIONAL)
			->addOption('force', 'f', NULL, 'Warning this deletes your data! Forces now used index to be deleted before new index is created and mapping set.')
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		$entityName = $input->getArgument('entityName');
		$forcedDelete = $input->getOption('force');


		if ($entityName) {
			if ($forcedDelete) {
				$this->elasticMapper->deleteIndex($this->entities[$entityName]);
			}
			$this->elasticMapper->createIndex($this->entities[$entityName]);

		} else {
			foreach ($this->entities as $entity) {
				if ($forcedDelete) {
					$this->elasticMapper->deleteIndex($entity);
				}
				$this->elasticMapper->createMapping($entity);
			}
		}
	}

}
