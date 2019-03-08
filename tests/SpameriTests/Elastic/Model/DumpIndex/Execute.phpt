<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\DumpIndex;


require_once __DIR__ . '/../../../bootstrap.php';


class Execute extends \Tester\TestCase
{

	public function testProcess() : void
	{
		$scroll = new \Spameri\Elastic\Model\Scroll(
			new \Spameri\Elastic\ClientProvider(
				new \Elasticsearch\ClientBuilder(),
				new \Spameri\Elastic\Settings\NeonSettingsProvider(
					'http://192.168.0.14',
					9200
				)
			),
			new \Spameri\ElasticQuery\Response\ResultMapper()
		);

		$dumpIndex = new \Spameri\Elastic\Model\DumpIndex(
			$scroll
		);
		$dumpIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$dumpIndex->execute('nay2014_entity_1', 'test.log', 'product');
	}


	public function tearDown() : void
	{
//		\Nette\Utils\FileSystem::delete('test.log');
		parent::tearDown();
	}

}

(new Execute())->run();
