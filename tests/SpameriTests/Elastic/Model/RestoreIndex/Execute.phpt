<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\RestoreIndex;


require_once __DIR__ . '/../../../bootstrap.php';


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
					\SpameriTests\Elastic\Config::INDEX_RESTORE
				)
			)->toArray()
		);
	}


	public function testProcess() : void
	{
		$dumpIndex = new \Spameri\Elastic\Model\RestoreIndex($this->clientProvider);
		$dumpIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$dumpIndex->execute(__DIR__ . '/restoreData.json', 500);

		$id = 192461;
		$result = $this->clientProvider->client()->get(
			(
				new \Spameri\ElasticQuery\Document(
					\SpameriTests\Elastic\Config::INDEX_RESTORE,
					NULL,
					(string) $id
				)
			)->toArray()
		);

		\Tester\Assert::same($id, $result['_source']['id']);
	}

	protected function tearDown()
	{
		$this->clientProvider->client()->indices()->delete(
			(
			new \Spameri\ElasticQuery\Document(
				\SpameriTests\Elastic\Config::INDEX_RESTORE
			)
			)->toArray()
		)
		;
	}
}

(new Execute())->run();
