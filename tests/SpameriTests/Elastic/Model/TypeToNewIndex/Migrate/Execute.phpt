<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\TypeToNewIndex\Migrate;


require_once __DIR__ . '/../../../../bootstrap.php';


class Execute extends \Tester\TestCase
{

	public function testProcess() : void
	{
		$clientProvider = new \Spameri\Elastic\ClientProvider(
			new \Elasticsearch\ClientBuilder(),
			new \Spameri\Elastic\Settings\NeonSettingsProvider(
				'http://192.168.0.14',
				9200
			)
		);
		$dateTimeProvider = new \Spameri\Elastic\Provider\DateTimeProvider(new \DateTimeImmutable());
		$resultMapper = new \Spameri\ElasticQuery\Response\ResultMapper();

		$migrate = new \Spameri\Elastic\Model\TypeToNewIndex\Migrate(
			new \Spameri\Elastic\Model\TypeToNewIndex\DocumentMigrateStatus(),
			$clientProvider,
			$dateTimeProvider,
			new \Spameri\Elastic\Model\Delete($clientProvider),
			new \Spameri\Elastic\Model\Get($clientProvider, $resultMapper),
			new \Spameri\Elastic\Model\Indices\Close($clientProvider),
			new \Spameri\Elastic\Model\Indices\GetMapping($clientProvider),
			new \Spameri\Elastic\Model\Indices\PutMapping($clientProvider),
			new \Spameri\Elastic\Model\Search($clientProvider, $resultMapper),
			new \Spameri\Elastic\Mapper\ElasticMapper($clientProvider, $dateTimeProvider),
			new \Spameri\Elastic\Model\Indices\Create($clientProvider),
			new \Spameri\Elastic\Model\Indices\Get($clientProvider),
			new \Spameri\Elastic\Model\Indices\PutSettings($clientProvider)
		);

		$migrate->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$migrate->execute(
			'nay2014_entity_1',
			'product',
			'nay2014_product_1',
			'nay2014_product_1',
			FALSE
		);
	}

}

(new Execute())->run();
