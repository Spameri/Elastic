<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class CreateIndex extends \Symfony\Component\Console\Command\Command
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
		array $entities
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
			->setName('spameri:elastic:create-index')
			->setDescription('Creates index and puts mapping for entity/ies.')
			->addArgument('entityName', \Symfony\Component\Console\Input\InputArgument::OPTIONAL)
			->addOption(
				'force', 'f', NULL,
				'Warning this deletes your data! Forces now used index to be deleted before new index is created and mapping set.'
			)
		;
	}


	/**
	 * @throws \Spameri\Elastic\Exception\ElasticSearch
	 */
	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		/** @var string $entityName */
		$entityName = $input->getArgument('entityName');
		$forcedDelete = $input->getOption('force');

		if ($entityName) {
			if ( ! isset($this->entities[$entityName])) {
				$output->writeln('Provided entity name ' . $entityName . ' is not found in configuration.');

			} else {
				if ($forcedDelete) {
					$this->elasticMapper->deleteIndex($this->entities[$entityName]['index']);
					$output->writeln('Index ' . $this->entities[$entityName]['index'] . ' deleted.');
				}
				try {
					$this->elasticMapper->createIndex($this->entities[$entityName]);
					$output->writeln('Index ' . $this->entities[$entityName]['index'] . ' created.');

				} catch (\Spameri\Elastic\Exception\ElasticSearchException $exception) {
					$output->writeln($exception->getMessage());
				}
			}

		} else {
			foreach ($this->entities as $entity) {
				if ($forcedDelete) {
					$this->elasticMapper->deleteIndex($entity['index']);
					$output->writeln('Index ' . $this->entities[$entityName]['index'] . ' deleted.');
				}
				try {
					$this->elasticMapper->createIndex($entity);
					$output->writeln('Index ' . $this->entities[$entityName]['index'] . ' created.');

				} catch (\Spameri\Elastic\Exception\ElasticSearchException $exception) {
					$output->writeln($exception->getMessage());
				}
			}
		}
	}

}
