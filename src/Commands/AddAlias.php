<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;


class AddAlias extends \Symfony\Component\Console\Command\Command
{

	private \Spameri\Elastic\Model\Indices\AddAlias $addAlias;


	public function __construct(
		\Spameri\Elastic\Model\Indices\AddAlias $addAlias
	)
	{
		parent::__construct(NULL);
		$this->addAlias = $addAlias;
	}


	protected function configure(): void
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
			$this->addAlias->execute($alias, $index);

		} catch (\Spameri\Elastic\Exception\AliasAlreadyExists $exception) {
			$output->writeln($exception->getMessage());
		}

		return 0;
	}

}
