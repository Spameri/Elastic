<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\TypeToNewIndex\Migrate;


require_once __DIR__ . '/../../../../bootstrap.php';


/**
 * @testCase
 */
class Execute extends \Tester\TestCase
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	protected function setUp()
	{
		$this->clientProvider = new \Spameri\Elastic\ClientProvider(
			new \Elasticsearch\ClientBuilder(),
			new \Spameri\Elastic\Settings\NeonSettingsProvider(
				\SpameriTests\Elastic\Config::HOST,
				9200
			)
		);
		$this->clientProvider->client()->indices()->create(
			(
			new \Spameri\ElasticQuery\Document(
				\SpameriTests\Elastic\Config::INDEX_MIGRATE
			)
			)->toArray()
		);

		$restoreIndex = new \Spameri\Elastic\Model\RestoreIndex($this->clientProvider);
		$restoreIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$restoreIndex->execute(__DIR__ . '/migrateData.json', 500);
	}


	public function testProcess() : void
	{
		$dateTimeProvider = new \Spameri\Elastic\Provider\DateTimeProvider(new \DateTimeImmutable());
		$resultMapper = new \Spameri\ElasticQuery\Response\ResultMapper();

		$migrate = new \Spameri\Elastic\Model\TypeToNewIndex\Migrate(
			new \Spameri\Elastic\Model\TypeToNewIndex\DocumentMigrateStatus(),
			$this->clientProvider,
			$dateTimeProvider,
			new \Spameri\Elastic\Model\Delete($this->clientProvider),
			new \Spameri\Elastic\Model\Get($this->clientProvider, $resultMapper),
			new \Spameri\Elastic\Model\Indices\Close($this->clientProvider),
			new \Spameri\Elastic\Model\Indices\GetMapping($this->clientProvider),
			new \Spameri\Elastic\Model\Indices\PutMapping($this->clientProvider),
			new \Spameri\Elastic\Model\Search($this->clientProvider, $resultMapper),
			new \Spameri\Elastic\Mapper\ElasticMapper($this->clientProvider, $dateTimeProvider),
			new \Spameri\Elastic\Model\Indices\Create($this->clientProvider),
			new \Spameri\Elastic\Model\Indices\Get($this->clientProvider),
			new \Spameri\Elastic\Model\Indices\PutSettings($this->clientProvider)
		);

		$id = '192489';
		$responseOldIndex = $this->clientProvider->client()->get(
			(
				new \Spameri\ElasticQuery\Document(
					\SpameriTests\Elastic\Config::INDEX_MIGRATE,
					NULL,
					\SpameriTests\Elastic\Config::TYPE,
					$id
				)
			)->toArray()
		);

		\Tester\Assert::same($id, $responseOldIndex['_id']);

		$migrate->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$migrate->execute(
			\SpameriTests\Elastic\Config::INDEX_MIGRATE,
			\SpameriTests\Elastic\Config::TYPE,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW,
			\SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW,
			\SpameriTests\Elastic\Config::TYPE,
			FALSE
		);

		$responseNewIndex = $this->clientProvider->client()->get(
			(
			new \Spameri\ElasticQuery\Document(
				\SpameriTests\Elastic\Config::INDEX_MIGRATE_NEW,
				NULL,
				\SpameriTests\Elastic\Config::TYPE,
				$id
			)
			)->toArray()
		);

		\Tester\Assert::same($id, $responseNewIndex['_id']);
	}


	protected function tearDown() : void
	{
		$this->clientProvider->client()->indices()->delete(
			(
			new \Spameri\ElasticQuery\Document(
				\SpameriTests\Elastic\Config::INDEX_MIGRATE . '*'
			)
			)->toArray()
		)
		;
	}

}

(new Execute())->run();
