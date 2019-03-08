<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Model\RestoreIndex;


require_once __DIR__ . '/../../../bootstrap.php';


class Execute extends \Tester\TestCase
{

	public function testProcess() : void
	{
		$dumpIndex = new \Spameri\Elastic\Model\RestoreIndex(
			new \Spameri\Elastic\ClientProvider(
				new \Elasticsearch\ClientBuilder(),
				new \Spameri\Elastic\Settings\NeonSettingsProvider(
					'http://192.168.0.14',
					9200
				)
			)
		);
		$dumpIndex->setOutput(new \Symfony\Component\Console\Output\ConsoleOutput());

		$dumpIndex->execute('test.log', 500);
	}

}

(new Execute())->run();
