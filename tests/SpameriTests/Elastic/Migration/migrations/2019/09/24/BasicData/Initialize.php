<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Migration\BasicData;

class Initialize extends \Spameri\Elastic\Migration\AbstractMigration
{

	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider,
		\Symfony\Component\Console\Output\ConsoleOutputInterface $output
	)
	{
		parent::__construct($clientProvider, $output);
	}


	public function migrate(): void
	{
		$document = new \Spameri\ElasticQuery\Document\Body\Plain([
			'databaseId' => 1,
			'name' => 'Avengers',
			'year' => 2019,
			'released' => TRUE,
		]);
		$this->clientProvider->client()->index($document->toArray());
	}

}
