<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class DeleteIndex extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var \Spameri\Elastic\Mapper\ElasticMapper
	 */
	private $elasticMapper;


	public function __construct(
		\Spameri\Elastic\Mapper\ElasticMapper $elasticMapper
	)
	{
		parent::__construct(NULL);
		$this->elasticMapper = $elasticMapper;
	}


	protected function configure() : void
	{
		$this
			->setName('spameri:elastic:delete-index')
			->setDescription('Deletes index by name or alias. Warning this deletes your data!')
			->addArgument('indexName', \Symfony\Component\Console\Input\InputArgument::IS_ARRAY)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output
	)
	{
		$indexNames = $input->getArgument('indexName');

		foreach ($indexNames as $indexName) {
			$this->elasticMapper->deleteIndex($indexName);
		}
	}

}
