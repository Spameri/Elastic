<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Migration\Structure;

class Initialize extends \Spameri\Elastic\Migration\AbstractMigration
{

	/**
	 * @var \Spameri\Elastic\Model\Indices\Delete
	 */
	private $delete;

	/**
	 * @var \Spameri\Elastic\Model\Indices\Create
	 */
	private $create;

	/**
	 * @var \Spameri\Elastic\Model\Indices\PutSettings
	 */
	private $settings;

	/**
	 * @var \Spameri\Elastic\Model\Indices\PutMapping
	 */
	private $mapping;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider,
		\Symfony\Component\Console\Output\ConsoleOutputInterface $output,
		\Spameri\Elastic\Model\Indices\Delete $delete,
		\Spameri\Elastic\Model\Indices\Create $create,
		\Spameri\Elastic\Model\Indices\PutSettings $settings,
		\Spameri\Elastic\Model\Indices\PutMapping $mapping
	)
	{
		parent::__construct($clientProvider, $output);
		$this->delete = $delete;
		$this->create = $create;
		$this->settings = $settings;
		$this->mapping = $mapping;
	}


	public function before(): void
	{
		parent::before();
		$this->delete->execute('spameri');
	}


	public function migrate(): void
	{
		$this->output->writeln('Creating mapping');
		$this->create->execute('spameri', []);

		$this->output->writeln('Put settings');
		$this->settings->execute('spameri', []);

		$this->output->writeln('Put mapping');
		$this->mapping->execute('spameri', []);
	}

}
