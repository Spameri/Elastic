<?php declare(strict_types = 1);

namespace Spameri\Elastic\Commands;

#[\Symfony\Component\Console\Attribute\AsCommand(
	name: 'spameri:elastic:add-alias',
	description: 'Adds alias to existing index.'
)]
class AddAlias extends \Symfony\Component\Console\Command\Command
{

	/**
	 * @var string
	 */
	protected static $defaultName = 'spameri:elastic:add-alias';


	public function __construct(
		private readonly \Spameri\Elastic\Model\Indices\AddAlias $addAlias,
	)
	{
		parent::__construct(self::$defaultName);
	}


	protected function configure(): void
	{
		$this
			->setName(self::$defaultName)
			->setDescription('Adds alias to existing index.')
			->addArgument('index', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
			->addArgument('alias', \Symfony\Component\Console\Input\InputArgument::REQUIRED)
		;
	}


	protected function execute(
		\Symfony\Component\Console\Input\InputInterface $input,
		\Symfony\Component\Console\Output\OutputInterface $output,
	): int
	{
		/** @var string $index */
		$index = $input->getArgument('index');
		/** @var string $alias */
		$alias = $input->getArgument('alias');

		try {
			$this->addAlias->execute($alias, $index);

		} catch (\Spameri\Elastic\Exception\AliasAlreadyExists $exception) {
			$output->writeln($exception->getMessage());
		}

		return 0;
	}

}
