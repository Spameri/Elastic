<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\DumpIndex;


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
		$restoreIndex = new \Spameri\Elastic\Model\RestoreIndex($this->clientProvider);
		$restoreIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$restoreIndex->execute(__DIR__ . '/dumpData.json', 500);
	}


	public function testProcess() : void
	{
		$scroll = new \Spameri\Elastic\Model\Scroll(
			$this->clientProvider,
			new \Spameri\ElasticQuery\Response\ResultMapper()
		);

		$dumpIndex = new \Spameri\Elastic\Model\DumpIndex(
			$scroll
		);
		$dumpIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$dumpIndex->execute(\SpameriTests\Elastic\Config::INDEX_DUMP, 'test.log', 'product');

		\Tester\Assert::true(\file_exists('test.log'));

		$dumpFile = \file_get_contents('test.log');
		$exploded = explode("\r\n", $dumpFile);
		$decoded = \Nette\Utils\Json::decode($exploded[10]);

		\Tester\Assert::same('192437', $decoded->index->_id);
	}


	protected function tearDown()
	{
		$this->clientProvider->client()->indices()->delete(
			(
			new \Spameri\ElasticQuery\Document(
				\SpameriTests\Elastic\Config::INDEX_DUMP
			)
			)->toArray()
		)
		;
		\Nette\Utils\FileSystem::delete('test.log');
	}

}

(new Execute())->run();
