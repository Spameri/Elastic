<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class AddAlias extends \Symfony\Component\Console\Command\Command
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
			->setName('spameri:elastic:add-alias')
			->setDescription('Adds alias to existing index.')
			->addArgument('index', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('alias', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input
		, \Symfony\Component\Console\Output\OutputInterface $output
	): int
	{
		/** @var string $index */
		$index = $input->getArgument('index');
		/** @var string $alias*/
		$alias = $input->getArgument('alias');

		try {
			$this->elasticMapper->addAlias($index, $alias);

		} catch (\Spameri\Elastic\Exception\AliasAlreadyExists $exception) {
			$output->writeln($exception->getMessage());
		}

		return 0;
	}

}
