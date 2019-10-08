<?php declare(strict_types = 1);

namespace Spameri\Elastic\Migration;

abstract class AbstractMigration implements MigrationFileInterface
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	protected $clientProvider;

	/**
	 * @var \Symfony\Component\Console\Output\ConsoleOutputInterface
	 */
	protected $output;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
		, \Symfony\Component\Console\Output\ConsoleOutputInterface $output
	)
	{
		$this->clientProvider = $clientProvider;
		$this->output = $output;
	}


	public function before(): void
	{
		$this->output->writeln('Starting migration ' . self::class);
		$this->output->writeln('Running before scripts');
	}


	public function after(): void
	{
		$this->output->writeln('Running after scripts');
		$this->output->writeln('Finished migration ' . self::class);
	}

}
